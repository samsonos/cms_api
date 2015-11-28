<?php
namespace samson\cms;

use samson\activerecord\dbRelation;
use samson\activerecord\dbMySQLConnector;
use samson\activerecord\Condition;
use samson\activerecord\Argument;

/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 05.09.14 at 17:57
 * @deprecated
 */

class Filter
{

    public $class_name = 'filter';

    /** @var array Array of filter objects */
    private static $filterCollection = array();

    /**
     * Adds filter by filterID to filter collection
     * @param int $filterId Filter identifier to be added
     * @return \samson\activerecord\filter
     */
    public static function add($filterId)
    {
        /** @var \samson\activerecord\filter $filter Filter object to add */
        $filter = null;

        // adds filter to collection if it is found
        if (dbQuery('filter')->cond('filter_id', $filterId)->first($filter)) {
            self::$filterCollection[] = $filter;
        }
        return $filter;
    }

    /**
     * Filters the data due to existing filters in class collection and returns material objects
     * which belong to specified structure.
     * Also some other filters can be added
     * @param \samson\activerecord\material $materials List of materials to filter
     * @param array(int) $structures Array of structure ID's to search related materials
     * @param array(int) $filters Array of filters. They will be added to existing filters.
     * @param string $handler Name of external handler.
     * @param array(mixed) $handlerParams Array of params to external handler
     * @return bool If there are some results function will return true, otherwise it will return false
     */
    public static function performFilters(
        &$materials,
        $structures = null,
        $filters = null,
        $handler = null,
        $handlerParams = null
    ) {

        /** @var array(\samson\activerecord\field) $fields Array of fields which acn be filtered */
        $fields = null;

        // If structures are represented by single type - make array from it
        $structures = is_array($structures) ? $structures : $structures = array($structures);
        // If there are some filters add them to filter collection
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                self::add($filter);
            }
        }

        // Try to full $fields
        dbQuery('field')->cond('filtered', 0, dbRelation::NOT_EQUAL)->exec($fields);
//        $query = dbQuery('materialfield')->join('field')->cond('filtered', 1);

        /** @var \samson\activerecord\dbQuery $query Query object to perform filters */
        $query = dbQuery('materialfield')->cond('FieldID', array_keys($fields));

        /** @var array(int) $materialsIDs Array of material IDs */
        $materialsIDs = array();

        /** @var \samson\activerecord\Condition $orCondition Condition object to set condition between filters */
        $orCondition = new Condition('OR');

        foreach (self::$filterCollection as $filter) {

            // if current field filter type is set
            if (!empty($fields[$filter->field_id]['filtered'])) {

                /** @var \samson\activerecord\Condition $andCondition Condition object to set condition inside filter */
                $andCondition = self::getFilterCondition($filter, $fields[$filter->field_id]['filtered']);
                $orCondition->add($andCondition);
            }
        }

        // Put found material IDs to created $materialsIDs array
        $query->cond($orCondition)->fields('MaterialID', $materialsIDs);

        /** @var \samson\activerecord\dbQuery $queryMaterials Query object to get Material objects */
        $queryMaterials = dbQuery('\samson\cms\CMSMaterial')->cond('MaterialID', $materialsIDs)->join('structurematerial');

        // If structures were set add them to query
        if (!empty($structures)) {
            $queryMaterials->cond('StructureID', $structures);
        }

        // If external request handler is passed - use it
        if (is_callable($handler)) {

            // If there are no handler parameters set them as empty array
            if (empty($handlerParams) || !isset($handlerParams)) {
                $handlerParams = array();
            }

            // Call external query handler
            if (call_user_func_array(
                $handler,
                array_merge(array(&$queryMaterials), array(self::$filterCollection), $handlerParams)
            ) === false) {

                // Someone else has failed my lord
                return false;
            }
        }

        // If there are results return true
        if ($queryMaterials->exec($materials)) {
            return true;
        }
        return false;
    }

    /**
     * @param \samson\activerecord\filter $filter
     * @param int $filterType Filter type of $filter parameter
     * @return \samson\activerecord\Condition Condition object created due to filter type
     */
    private static function getFilterCondition($filter, $filterType = 1)
    {
        /** @var \samson\activerecord\Condition $condition */
        $condition = new Condition();

        // Try to determine type of filter
        switch ($filterType) {
            case '1':
                $condition
                    ->add(new Argument('FieldID', $filter->field_id))
                    ->add(new Argument('Value', $filter->value));
                break;
            case '2':
                $condition
                    ->add(new Argument('FieldID', $filter->field_id))
                    ->add(new Argument('Value', $filter->value, dbRelation::NOT_EQUAL));
                break;
            case '3':
                $condition
                    ->add(new Argument('FieldID', $filter->field_id))
                    ->add(new Argument('Value', $filter->value, dbRelation::LOWER));
                break;
            case '4':
                $condition
                    ->add(new Argument('FieldID', $filter->field_id))
                    ->add(new Argument('Value', $filter->value, dbRelation::GREATER));
                break;
        }
        return $condition;
    }

    /**
     * Creates new filter by value and fieldID.
     * Both parameters must be in `materialfield` table.
     * Combination of this parameters must be unique.
     * @param int $fieldId Field identifier
     * @param mixed $fieldValue Filtered value
     */
    public static function createFilter($fieldId, $fieldValue)
    {
        // If there are no such filters
        if (!empty($fieldId) && !empty($fieldValue) &&
            !dbQuery('filter')->cond('field_id', $fieldId)->cond('value', $fieldValue)->first()) {

            /** @var \samson\activerecord\filter $filter */
            $filter = new \samson\activerecord\filter();
            $filter->filter_id = $fieldId;
            $filter->value = $fieldValue;
            $filter->save();
        }
    }

    /**
     * Fills filter table by existing data
     */
    public static function resetFilters()
    {
        /** @var \samson\activerecord\materialfield $filters Array of materialfield objects to fill filter table */
        $filters = null;

        // TODO: add filter generation by filter type

        // check if such data exists in materialfield table
        if (dbQuery('materialfield')
            ->join('field')
            ->cond('filtered', 1)
            ->group_by('Value')
            ->exec($filters)) {

            // if yes - generate `filter` table
            db()->simple_query('TRUNCATE TABLE `'.dbMySQLConnector::$prefix.'filter`');
            foreach ($filters as $filter) {

                /** @var \samson\activerecord\filter $newFilter */
                $newFilter = new filter();
                $newFilter->field_id = $filter->FieldID;
                $newFilter->value = $filter->Value;
                $newFilter->save();
            }
        }
    }

    /**
     * Gets list of filters by specified field
     * @param int $fieldId Field to search
     * @return array(\samson\activerecord\filter) Array of filters
     */
    public static function getFiltersByField($fieldId)
    {
        /** @var array $values Array of filters */
        $values = array();

        // If there are some unique values
        if (!empty($fieldId)) {
            dbQuery('filter')->cond('field_id', $fieldId)->exec($values);
        }
        return $values;
    }
}
