<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 26.12.2014
 * Time: 16:22
 */

namespace samson\cms;


use samson\activerecord\Condition;
use samson\activerecord\dbRelation;

/**
 * Collection query builder for filtering
 * @package samson\cms
 * @author Egorov Vitaly <egorov@samsonos.com>
 */
class CollectionQuery
{
    /** @var array Collection for current filtered material identifiers */
    protected $materialIDs = array();

    /** @var array Collection of navigation filters */
    protected $navigation = array();

    /** @var array Collection of query handlers */
    protected $handlers = array();

    /** @var callback External material handler */
    protected $materialHandler = array();

    /** @var array Collection of field filters */
    protected $field = array();

    /**
     * Add external handler
     * @param callback $handler
     * @param array $params
     * @return self Chaining
     */
    public function handler($handler, array $params)
    {
        $this->handlers[] = array($handler, $params);

        return $this;
    }

    /**
     * Set external material handler
     * @param callback $handler
     * @param array $params
     * @return self Chaining
     */
    public function materialHandler($handler, array $params)
    {
        $this->materialHandler = array_merge(array($handler), $params);

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
        if(isset($navigation{0})) {
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
        if(isset($field{0})) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('FieldID', $field)->add('Name', $field);

            /** @var \samson\activerecord\field $navigation */
            $navigation = null;
            if (dbQuery('field')->cond($idOrUrl)->first($field)) {
                // Store retrieved field element and its value as field collection filter
                $this->field[] = array($field, $value, $relation);
            }
        }

        // Chaining
        return $this;
    }

    /**
     * Perform collection database retrieval using setted filters
     * @param array $collection Return value
     * @return bool|mixed
     */
    public function exec(& $collection = array())
    {
        // Create navigation material query
        $query = dbQuery('structurematerial');

        // Clear current materials identifiers list
        $this->materialIDs = array();

        // Iterate all applied navigation filters
        foreach ($this->navigation as $navigation) {
            // Perform request to get next portion of filtered material identifiers
            if ($query->cond('StructureID', $navigation)->fieldsNew('MaterialID', $this->materialIDs)) {
                // Apply retrieved material ids to next query
                $query->cond('MaterialID', $this->materialIDs);
            } else { // This filter applying failed
                return false;
            }
        }

        // Create navigation material query
        $query = dbQuery('materialfield');

        // Apply current filtered material identifiers
        if (sizeof($this->materialIDs)) {
           $query->cond('MaterialID', $this->materialIDs);
        }

        // Iterate all applied field filters
        foreach ($this->field as $field) {
            // Get field
            $valueField = $field[0]->Type == 7 ? 'numeric_value' : 'value';
            // Perform request to get next portion of filtered material identifiers
            if ($query->cond('FieldID', $field[0]->id)
                ->cond($valueField, $field[1], $field[2])
                ->group_by('MaterialID')
                ->fieldsNew('MaterialID', $this->materialIDs)
            ) {
                // Apply retrieved material ids to next query
                $query->cond('MaterialID', $this->materialIDs);
            } else { // This filter applying failed
                return false;
            }
        }

        // Call external handlers chain
        foreach($this->handlers as $handler) {
            $this->materialIDs = call_user_func_array(
                $handler[0],
                array_merge(array(&$this->materialIDs), $handler[1])
            );
        }


        // Create final material query
        $query = dbQuery('\samson\cms\CMSMaterial');

        // Set external material query handler
        if (is_callable($this->materialHandler[0])) {
            call_user_func_array(array($query, 'handler'), $this->materialHandler);
        }

        // Return final filtered material query result
        return $query->cond('MaterialID')->exec($collection);
    }
}
