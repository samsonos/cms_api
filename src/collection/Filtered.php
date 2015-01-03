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
 * @package samson\cms
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
        if (isset($navigation{0})) {
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
        if (isset($field{0})) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('FieldID', $field)->add('Name', $field);

            /** @var \samson\activerecord\field $field */
            $field = null;
            if (dbQuery('field')->cond($idOrUrl)->first($field)) {
                // Store retrieved field element and its value as field collection filter
                $this->field[] = array($field, $value, $relation);
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
            $query = dbQuery('structurematerial');

            // If we have already filtered material identifiers
            if (sizeof($filteredIds)) {
                // Apply them to query
                $query->cond('MaterialID', $filteredIds);
            }

            // Perform request to get next portion of filtered material identifiers
            if (!$query
                ->cond('StructureID', $navigation)
                ->cond('Active', 1)
                ->fieldsNew('MaterialID', $filteredIds)
            ) {
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
            // Get field value column
            $valueField = $field[0]->Type == 7 || $field[0]->Type == 3 ? 'numeric_value' : 'value';

            // Create material-field query
            $query = dbQuery('materialfield');

            // If we have already filtered material identifiers
            if (sizeof($filteredIds)) {
                // Apply them to query
                $query->cond('MaterialID', $filteredIds);
            }

            // Perform request to get next portion of filtered material identifiers
            if (!$query->cond('FieldID', $field[0]->id)
                ->cond($valueField, $field[1], $field[2])
                ->group_by('MaterialID')
                ->fieldsNew('MaterialID', $filteredIds)
            ) {
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
    public function applyFilter(& $filteredIds = array())
    {
        return $this->applyNavigationFilter($filteredIds)
            && $this->applyFieldFilter($filteredIds);
    }

    /**
     * Perform collection database retrieval using set filters
     * @return array $collection Return value
     */
    public function fill()
    {
        // Clear current materials identifiers list
        $this->materialIDs = array();

        // Perform material filtering
        if ($this->applyFilter($this->materialIDs)) {
            // Now we have all possible material filters applied and final material identifiers collection

            // Call external handlers
            foreach ($this->idHandlers as $handler) {
                // Call external handlers chain
                if (!call_user_func_array(
                    $handler[0],
                    array_merge(array(&$this->materialIDs), $handler[1]) // Pass material identifiers
                )) {
                    // Stop - if one of external handlers has failed
                    return array();
                }
            }

            // Create final material query
            $query = dbQuery($this->entityName);

            // If we have material id filter
            if (sizeof($this->materialIDs)) {
                // Add them to query
                $query->cond('MaterialID', $this->materialIDs);
            }

            // Call external handlers chain
            foreach ($this->entityHandlers as $handler) {
                call_user_func_array(
                    $handler[0],
                    array_merge(array(&$query), $handler[1])
                );
            }

            // Return final filtered entity query result
            return $query->cond('Active', 1)->exec();
        }

        // Something failed
        return array();
    }
}
