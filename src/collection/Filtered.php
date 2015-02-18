<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 26.12.2014
 * Time: 16:22
 */
namespace samsonos\cms\collection;

use samson\activerecord\Condition;
use samson\activerecord\dbRelation;

/**
 * Collection query builder for filtering
 * @package samsonos\cms\collection
 * @author Egorov Vitaly <egorov@samsonos.com>
 */
class Filtered extends Generic
{
    /** @var array Collection for current filtered material identifiers */
    protected $materialIDs = array();

    /** @var array Collection of navigation filters */
    protected $navigation = array();

    /** @var array Collection of field filters */
    protected $field = array();

    /** @var array Collection of query handlers */
    protected $idHandlers = array();

    /** @var array External material handler and params array */
    protected $entityHandlers = array();

    /** @var string Collection entities class name */
    protected $entityName = 'samson\cms\CMSMaterial';

    /**
     * Add external identifier filter handler
     * @param callback $handler
     * @param array $params
     * @return self Chaining
     */
    public function handler($handler, array $params = array())
    {
        // Add callback with parameters to array
        $this->idHandlers[] = array($handler, $params);

        return $this;
    }

    /**
     * Set external entity handler
     * @param callback $handler
     * @param array $params
     * @return self Chaining
     */
    public function entityHandler($handler, array $params = array())
    {
        // Add callback with parameters to array
        $this->entityHandlers[] = array($handler, $params);

        return $this;
    }

    /**
     * Filter collection using navigation entity or collection of them.
     * If collection of navigation Url or Ids is passed then this group will be
     * applied as single navigation filter to retrieve materials.
     *
     * @param string|integer|array $navigation Navigation URL or identifier for filtering
     * @return self Chaining
     */
    public function navigation($navigation)
    {
        // Do not allow empty strings
        if (isset($navigation{0}) || is_numeric($navigation)) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('StructureID', $navigation)->add('Url', $navigation);

            /** @var array $navigationIds  */
            $navigationIds = null;
            if (dbQuery('structure')->cond($idOrUrl)->fieldsNew('StructureID', $navigationIds)) {
                // Store all retrieved navigation elements as navigation collection filter
                $this->navigation[] = $navigationIds;
            }
        }

        // Chaining
        return $this;
    }

    /**
     * Filter collection using additional field entity.
     *
     * @param string|integer $field Additional field identifier or name
     * @param mixed $value Additional field value for filtering
     * @param string $relation Additional field relation for filtering
     * @return self Chaining
     */
    public function field($field, $value, $relation = dbRelation::EQUAL)
    {
        // Do not allow empty strings
        if (isset($field{0}) || is_numeric($field)) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('FieldID', $field)->add('Name', $field);

            /** @var \samson\activerecord\field $field */
            $field = null;
            if (dbQuery('field')->cond($idOrUrl)->first($field)) {
                // Get field value column
                $valueField = in_array($field->Type, array(3, 7)) ? 'numeric_value' : 'value';

                /** @var Condition $condition Ranged condition */
                $condition = new Condition('AND');

                // Add min value for ranged condition
                $condition->add($valueField, $value, $relation);

                // Store retrieved field element and its value as field collection filter
                $this->field[] = array($field, $condition);
            }
        }

        // Chaining
        return $this;
    }

    /**
     * Filter collection of numeric field in range from min to max values
     * @param string|integer $field Additional field identifier or name
     * @param integer $minValue Min value for range filter
     * @param integer $maxValue Max value for range filter
     * @return self Chaining
     */
    public function ranged($field, $minValue, $maxValue)
    {
        if (($minValue <= $maxValue) && (isset($field{0}) || is_numeric($field))) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('FieldID', $field)->add('Name', $field);

            /** @var \samson\activerecord\field $field */
            $field = null;
            if (dbQuery('field')->cond($idOrUrl)->cond('Type', array(3, 7))->first($field)) {
                /** @var Condition $condition Ranged condition */
                $condition = new Condition('AND');

                // Add min value for ranged condition
                $condition->add('numeric_value', $minValue, dbRelation::GREATER_EQ);

                // Add max value for ranged condition
                $condition->add('numeric_value', $maxValue, dbRelation::LOWER_EQ);

                // Store created condition
                $this->field[] = array($field, $condition);
            }
        }

        // Chaining
        return $this;
    }

    /**
     * Try to get all material identifiers filtered by navigation
     * if no navigation filtering is set - nothing will happen.
     *
     * @param array $filteredIds Collection of filtered material identifiers
     * @return bool True if ALL navigation filtering succeeded or there was no filtering at all otherwise false
     */
    protected function applyNavigationFilter(& $filteredIds = array())
    {
        // Iterate all applied navigation filters
        foreach ($this->navigation as $navigation) {
            // Create navigation-material query
            $query = dbQuery('structurematerial')

                ->cond('StructureID', $navigation)
                ->cond('Active', 1)
                ->group_by('MaterialID')
            ;

	        if (isset($filteredIds)) {
		        $query->cond('MaterialID', $filteredIds);
	        }

	        // Perform request to get next portion of filtered material identifiers
            if (!$query->fieldsNew('MaterialID', $filteredIds)) {
                // This filter applying failed
                return false;
            }
        }

        // We have no navigation collection filters
        return true;
    }

    /**
     * Try to get all material identifiers filtered by additional field
     * if no field filtering is set - nothing will happen.
     *
     * @param array $filteredIds Collection of filtered material identifiers
     * @return bool True if ALL field filtering succeeded or there was no filtering at all otherwise false
     */
    protected function applyFieldFilter(& $filteredIds = array())
    {
        // Iterate all applied field filters
        foreach ($this->field as $field) {
            // Create material-field query
            $query = dbQuery('materialfield')
                ->cond('FieldID', $field[0]->id)
                ->cond($field[1])
                ->group_by('MaterialID')
            ;

	        if (isset($filteredIds)) {
		        $query->cond('MaterialID', $filteredIds);
	        }

            // Perform request to get next portion of filtered material identifiers
            if (!$query->fieldsNew('MaterialID', $filteredIds)) {
                // This filter applying failed
                return false;
            }
        }

        // We have no field collection filters
        return true;
    }

    /**
     * Apply all possible material filters
     * @param array $filteredIds Collection of material identifiers
     * @return bool True if ALL filtering succeeded or there was no filtering at all otherwise false
     */
    protected function applyFilter(& $filteredIds = array())
    {
        return $this->applyNavigationFilter($filteredIds)
            && $this->applyFieldFilter($filteredIds);
    }

    /**
     * Call handlers stack
     * @param array $handlers Collection of callbacks with their parameters
     * @param array $params External parameters to pass to callback at first
     * @return bool True if all handlers succeeded
     */
    protected function callHandlers(& $handlers = array(), $params = array())
    {
        // Call external handlers
        foreach ($handlers as $handler) {
            // Call external handlers chain
            if (!call_user_func_array(
                $handler[0],
                array_merge($params, $handler[1]) // Merge params and handler params
            )) {
                // Stop - if one of external handlers has failed
                return false;
            }
        }

        return true;
    }

    /**
     * Perform collection database retrieval using set filters
     * @return array $collection Return value
     */
    public function fill()
    {
        // Clear current materials identifiers list
        $this->materialIDs = null;

        // Perform material filtering
        if ($this->applyFilter($this->materialIDs)) {
            // Now we have all possible material filters applied and final material identifiers collection

            // Store filtered collection size
            $this->count = sizeof($this->materialIDs);

            // Call material identifier handlers
            $this->callHandlers($this->idHandlers, array(&$this->materialIDs));

            // Create final material query
            $query = dbQuery($this->entityName)->cond('MaterialID', $this->materialIDs);

            // Call material query handlers
            $this->callHandlers($this->entityHandlers, array(&$query));

            // Return final filtered entity query result
            return $query->cond('Active', 1)->exec();
        }

	    // Clear current materials identifiers list
	    $this->materialIDs = array();
        // Something failed
        return array();
    }
}
