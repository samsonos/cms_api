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
use samsonframework\collection\Paged;
use samsonframework\pager\PagerInterface;
use samsonframework\core\RenderInterface;
use samsonframework\orm\QueryInterface;

/**
 * Collection query builder for filtering
 * @package samsonos\cms\collection
 * @author Egorov Vitaly <egorov@samsonos.com>
 */
class Filtered extends Paged
{
    /** @var array Collection for current filtered material identifiers */
    protected $materialIDs = array();

    /** @var array Collection of navigation filters */
    protected $navigation = array();

    /** @var array Collection of field filters */
    protected $field = array();

    /** @var array Search string collection */
    protected $search = array();

    /** @var array Collection of query handlers */
    protected $idHandlers = array();

    /** @var array External material handler and params array */
    protected $entityHandlers = array();

    /** @var string Collection entities class name */
    protected $entityName = 'samson\cms\CMSMaterial';

    /** @var array Sorter parameters collection */
    protected $sorter = array();

    /**
     * Generic collection constructor
     * @param RenderInterface $renderer View render object
     * @param QueryInterface $query Query object
     */
    public function __construct(RenderInterface $renderer, QueryInterface $query, PagerInterface $pager)
    {
        // Call parent initialization
        parent::__construct($renderer, $query->className('material'), $pager);
    }

    /**
     * Render products collection block
     * @param string $prefix Prefix for view variables
     * @param array $restricted Collection of ignored keys
     * @return array Collection key => value
     */
    public function toView($prefix = null, array $restricted = array())
    {
        // Render pager and collection
        return array(
            $prefix.'html' => $this->render(),
            $prefix.'pager' => $this->pager->toHTML()
        );
    }

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
     * Set collection sorter parameters
     * @param string|integer $field Field identifier or name
     * @param string $destination ASC|DESC
     * @return void
     */
    public function sorter($field, $destination = 'ASC')
    {
        /**@var \samson\activerecord\field $field */
        // TODO: Add ability to sort with entity fields
        if (in_array($field, array('Modyfied', 'Created', 'Url', 'Name'))) {
            $this->sorter = array(
                'field' => $field,
                'name' => $field,
                'destination' => $destination
            );
        } else if ($this->isFieldObject($field)) {
            $this->sorter = array(
                'entity' => $field,
                'name' => $field->Name,
                'field' => in_array($field->Type, array(3, 7, 10)) ? 'numeric_value' : 'value',
                'destination' => $destination
            );
        }
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
        if (!empty($navigation)) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('StructureID', $navigation)->add('Url', $navigation);

            /** @var array $navigationIds  */
            $navigationIds = null;
            if ($this->query->className('structure')->cond($idOrUrl)->fieldsNew('StructureID', $navigationIds)) {
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
     * @param string|integer|\samson\cms\Field $field Additional field identifier or name
     * @param mixed $value Additional field value for filtering
     * @param string $relation Additional field relation for filtering
     * @return self Chaining
     */
    public function field($field, $value, $relation = dbRelation::EQUAL)
    {
        // Do not allow empty strings
        if ($this->isFieldObject($field)) {
            // Get field value column
            $valueField = in_array($field->Type, array(3, 7, 10)) ? 'numeric_value' : 'value';
			$valueField = $field->Type == 6 ? 'key_value' : $valueField;
			
            /** @var Condition $condition Ranged condition */
            $condition = new Condition('AND');

            // Add min value for ranged condition
            $condition->add($valueField, $value, $relation);

            // Store retrieved field element and its value as field collection filter
            $this->field[] = array($field, $condition);
        }

        // Chaining
        return $this;
    }

    /**
     * Filter collection using additional field entity values and LIKE relation.
     * If this method is called more then once, it will use materials, previously filtered by this method.
     *
     * @param string $search Search string
     * @return self Chaining
     */
    public function search($search)
    {
        // If input parameter is a string add it to search string collection
        if (isset($search{0})) {
            $this->search[] = $search;
        }

        // Chaining
        return $this;
    }

    /**
     * Filter collection of numeric field in range from min to max values
     * @param string|integer|\samson\cms\Field $field Additional field identifier or name
     * @param integer $minValue Min value for range filter
     * @param integer $maxValue Max value for range filter
     * @return self Chaining
     */
    public function ranged($field, $minValue, $maxValue)
    {
        // Check input parameters and try to find field
        if (($minValue <= $maxValue) && $this->isFieldObject($field)) {
            // TODO: Remove integers from code, handle else
            // Only numeric fields are supported
            if (in_array($field->Type, array(3, 7, 10))) {
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
     * Try to find additional field record
     * @param string|integer $field Additional field identifier or name
     * @return bool True if field record has been found
     */
    protected function isFieldObject(&$field)
    {
        // Do not allow empty strings
        if (!empty($field)) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('FieldID', $field)->add('Name', $field);

            // Perform query
            return $this->query->className('field')->cond($idOrUrl)->first($field);
        }

        // Field not found
        return false;
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
            $this->query->className('structurematerial')

                ->cond('StructureID', $navigation)
                ->cond('Active', 1)
                ->group_by('MaterialID')
            ;

            if (isset($filteredIds)) {
                $this->query->cond('MaterialID', $filteredIds);
            }

            // Perform request to get next portion of filtered material identifiers
            if (!$this->query->fieldsNew('MaterialID', $filteredIds)) {
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
            $this->query->className('materialfield')
                ->cond('FieldID', $field[0]->id)
                ->cond($field[1])
                ->group_by('MaterialID')
            ;

            if (isset($filteredIds)) {
                $this->query->cond('MaterialID', $filteredIds);
            }

            // Perform request to get next portion of filtered material identifiers
            if (!$this->query->fieldsNew('MaterialID', $filteredIds)) {
                // This filter applying failed
                return false;
            }
        }

        // We have no field collection filters
        return true;
    }

    /**
     * Try to find all materials which have fields similar to search strings
     *
     * @param array $filteredIds Collection of filtered material identifiers
     * @return bool True if ALL field filtering succeeded or there was no filtering at all otherwise false
     */
    protected function applySearchFilter(& $filteredIds = array())
    {
        /** @var array $fields Variable to store all fields related to set navigation */
        $fields = array();
        /** @var array $navigationArray Array of set navigation identifiers */
        $navigationArray = array();
        /** @var array $fieldFilter Array of filtered material identifiers via materialfield table */
        $fieldFilter = array();
        /** @var array $materialFilter Array of filtered material identifiers via material table */
        $materialFilter = array();

        // If there are at least one search string
        if (!empty($this->search)) {
            // Create array containing all navigation identifiers
            foreach ($this->navigation as $navigation) {
                $navigationArray = array_merge($navigationArray, $navigation);
            }

            // Get all related fields
            $this->query->className('structurefield')
                ->cond('StructureID', $navigationArray)
                ->group_by('FieldID')
                ->fieldsNew('FieldID', $fields);

            // Iterate over search strings
            foreach ($this->search as $searchString) {
                // Try to find search value in materialfield table
                $this->query->className('materialfield')
                    ->cond('FieldID', $fields)
                    ->cond('MaterialID', $filteredIds)
                    ->cond('Value', '%' . $searchString . '%', dbRelation::LIKE)
                    ->cond('Active', 1)
                    ->group_by('MaterialID')
                    ->fieldsNew('MaterialID', $fieldFilter);

                // TODO: Add generic support for all native fields or their configuration
                // Condition to search in material table by Name and URL
                $materialCondition = new Condition('OR');
                $materialCondition->add('Name', '%' . $searchString . '%', dbRelation::LIKE)
                    ->add('Url', '%' . $searchString . '%', dbRelation::LIKE);

                // Try to find search value in material table
                $this->query->className('material')
                    ->cond('MaterialID', $filteredIds)
                    ->cond($materialCondition)
                    ->cond('Active', 1)
                    ->fieldsNew('MaterialID', $materialFilter);

                // If there are no materials with specified conditions
                if (empty($materialFilter) && empty($fieldFilter)) {
                    // Filter applying failed
                    return false;
                } else {// Otherwise set filtered material identifiers
                    $filteredIds = array_unique(array_merge($materialFilter, $fieldFilter));
                }
            }
        }

        // We have no search collection filters
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
            && $this->applyFieldFilter($filteredIds)
            && $this->applySearchFilter($filteredIds);
    }

    /**
     * Perform material identifiers collection sorting
     * @param array $materialIDs Variable to return sorted collection
     */
    protected function applySorter(& $materialIDs = array())
    {
        // Check if sorter is configured
        if (sizeof($this->sorter)) {
            // If we need to sort by entity own field(column)
            // TODO: Get this list of entity field dynamically
            if (in_array($this->sorter['field'], array('Modyfied', 'Created', 'Url', 'Name'))) {
                // Sort material identifiers by its own table fields
                $this->query->className('material')
                    ->cond('Active', 1)
                    ->cond('MaterialID', $materialIDs)
                    ->order_by($this->sorter['field'], $this->sorter['destination'])
                    ->fieldsNew('MaterialID', $materialIDs);

            // Perform additional field ordered db request
            } else if ($this->query->className('materialfield')
                ->cond('FieldID', $this->sorter['entity']->id)
                ->order_by($this->sorter['field'], $this->sorter['destination'])
                ->cond('MaterialID', $materialIDs)
                ->fieldsNew('MaterialID', $materialIDs)) {
                // Perform some logic?
            }
        }
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
            if (call_user_func_array(
                $handler[0],
                array_merge($params, $handler[1]) // Merge params and handler params
            ) === false) {
                // Stop - if one of external handlers has failed
                return false;
            }
        }

        return true;
    }

    /**
     * Perform collection database retrieval using set filters
     *
     * @return self Chaining
     */
    public function fill()
    {
        // Clear current materials identifiers list
        $this->materialIDs = null;

        // TODO: Change this to new OOP approach
        $class = $this->entityName;

        // If no filters is set
        if (!sizeof($this->search) && !sizeof($this->navigation) && !sizeof($this->field)) {
            // Get all entity records identifiers
            $this->materialIDs = $this->query->fields($class::$_primary);
        }

        // Perform material filtering
        if ($this->applyFilter($this->materialIDs)) {
            // Now we have all possible material filters applied and final material identifiers collection

            // Store filtered collection size
            $this->count = sizeof($this->materialIDs);

            // Call material identifier handlers
            $this->callHandlers($this->idHandlers, array(&$this->materialIDs));

            // Filter all materials by active column
            $this->materialIDs = $this->query
                ->className($this->entityName)
                ->cond('Active', 1)
                ->cond('system', 0)
                ->cond($class::$_primary, $this->materialIDs)
                ->fields($class::$_primary);

            /*
            // Get system navigation items
            $navIds = $this->query->className('structure')->cond('system', '1')->fields('StructureID');

            // Remove all system materials from material identifiers collection
            $this->materialIDs = array_diff(
                $this->materialIDs,
                $this->query->className('structurematerial')->cond('StructureID', $navIds)->fields('MaterialID')
            );
            */

            // Perform sorting
            $this->applySorter($this->materialIDs);

            // Create count request to count pagination
            $this->pager->update(sizeof($this->materialIDs));

            // Cut only needed materials identifiers from array
            $this->materialIDs = array_slice($this->materialIDs, $this->pager->start, $this->pager->end);

            // Create final material query
            $this->query->className($this->entityName)->cond($class::$_primary, $this->materialIDs);

            // Call material query handlers
            $this->callHandlers($this->entityHandlers, array(&$this->query));

            // Add query sorter for showed page
            if (sizeof($this->sorter)) {
                $this->query->order_by($this->sorter['name'], $this->sorter['destination']);
            }

            // Return final filtered entity query result
            $this->collection = $this->query->exec();

        } else { // Collection is empty

            // Clear current materials identifiers list
            $this->materialIDs = array();

            // Updated pagination
            $this->pager->update(sizeof($this->materialIDs));
        }

        // Chaining
        return $this;
    }
}
