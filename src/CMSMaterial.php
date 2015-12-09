<?php 
namespace samson\cms;

use samson\activerecord\Condition;
use samson\core\iModuleViewable;
use samson\activerecord\dbRecord;

/**
 * Class for managing CMS material with all related data
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @version 0.9.1
 * @deprecated
 */
class CMSMaterial extends Material implements iModuleViewable
{
    /** Override table attributes for late static binding */
    public static $_attributes = array();
    public static $_sql_select = array();
    public static $_sql_from = array();
    public static $_own_group = array();
    public static $_map = array();

    /**
     * Find all related material to current
     * @param function $handler External DB request handler
     * @return array Collection of related materials
     */
    public function &related($handler = null)
    {
        $db_materials = array();

        //$GLOBALS['show_sql'] = true;

        // Create DB query
        $q = dbQuery('samson\cms\cmsrelatedmaterial');

        // If external query handler is passed
        if (isset($handler)) $q->handler($handler);

        // If we have found related materials
        if ($q->first_material($this->id)->fields('second_material', $ids)) {
            // Get related CMSMaterials by ids
            $db_materials = cmsquery()->MaterialID($ids)->published();
        }

        return $db_materials;
    }

    /**
     * Find all materials that current material relates to
     * @param function $handler External DB request handler
     * @return \samson\cms\CMSMaterial[] Collection of materials that current material relates to
     */
    public function &relates($handler = null)
    {
        $db_materials = array();

        //$GLOBALS['show_sql'] = true;

        // Create DB query
        $q = dbQuery('samson\cms\cmsrelatedmaterial');

        // If external query handler is passed
        if (isset($handler)) $q->handler($handler);

        // If we have found related materials
        if ($q->second_material($this->id)->fields('first_material', $ids)) {
            // Get related CMSMaterials by ids
            $db_materials = array_merge($db_materials, cmsquery()->MaterialID($ids)->published());
        }

        return $db_materials;
    }

    /**
     * Function to delete CMSMaterial completely with it's materialfield records
     */
    public function deleteWithRelations()
    {
        /** @var array $fields Array of materilfields of this material */
        $fields = null;
        /** @var int $count Variable to store count of materialfields */
        $count = 0;
        /** @var string $queryString Query to delete all materialfields */
        $queryString = 'DELETE FROM `' . \samson\activerecord\dbMySQLConnector::$prefix . 'materialfield` WHERE';
        /** @var int $materialId Current material identifier */
        $materialId = $this->MaterialID;

        $this->delete();

        if (dbQuery('materialfield')->cond('MaterialID', $materialId)->exec($fields)) {
            /** @var \samson\activerecord\materialfield $field Variable to store materailfield object */
            foreach ($fields as $field) {
                $count++;
                if ($count >= count($fields)) {
                    $queryString .= ' `MaterialFieldID`=' . $field->MaterialFieldID;
                } else {
                    $queryString .= ' `MaterialFieldID`=' . $field->MaterialFieldID . ' OR';
                }
            }
            db()->simple_query($queryString);
        }

        $queryString = 'DELETE FROM `' . \samson\activerecord\dbMySQLConnector::$prefix . 'structurematerial` WHERE';
        $count = 0;
        /** @var array $structures Array of structurematerials of this material */
        $structures = null;
        if (dbQuery('structurematerial')->cond('MaterialID', $materialId)->exec($structures)) {
            /** @var \samson\activerecord\structurematerial $structure Variable to store structurematerial object */
            foreach ($structures as $structure) {
                $count++;
                if ($count >= count($structures)) {
                    $queryString .= ' `StructureMaterialID`=' . $structure->StructureMaterialID;
                } else {
                    $queryString .= ' `StructureMaterialID`=' . $structure->StructureMaterialID . ' OR';
                }
            }
            db()->simple_query($queryString);
        }
    }

    /**
     * Function to retrieve this material table by specified field
     * @param string $tableSelector Selector to identify table structure
     * @param string $selector Database field by which search is performed
     * @param array $tableColumns Columns names list
     * @param string $externalHandler External handler to perform some extra code
     * @param array $params External handler params
     * @return array Collection of collections of table cells, represented as materialfield objects
     * @deprecated Use new \samsoncms\api\FieldTable()
     */
    public function getTable(
        $tableSelector,
        $selector = 'StructureID',
        &$tableColumns = null,
        $externalHandler = null,
        $params = array()
    ) {
        /** @var array $resultTable Collection of collections of field cells */
        $resultTable = array();
        /** @var array $dbTableFieldsIds Array of table structure column identifiers */
        $dbTableFieldsIds = array();

        // Get structure object if we need to search it by other fields
        if ($selector != 'StructureID') {
            $structure = dbQuery('structure')->cond($selector, $tableSelector)->first();
            $tableSelector = $structure->id;
        }

        /** If this table has columns */
        if (dbQuery('structurefield')
            ->cond("StructureID", $tableSelector)
            ->fields('FieldID', $dbTableFieldsIds)
        ) {
            // Get localized and not localized fields
            $localizedFields = array();
            $unlocalizedFields = array();
            /** @var \samson\cms\CMSField $dbTableField Table column */
            foreach (dbQuery('field')->order_by('priority')->cond('FieldID', $dbTableFieldsIds)->exec() as $field) {
                /** Add table columns names */
                $tableColumns[] = $field->Name;
                if ($field->local == 1) {
                    $localizedFields[] = $field->id;
                } else {
                    $unlocalizedFields[] = $field->id;
                }
            }

            // Query to get table rows(table materials)
            $tableQuery = dbQuery('material')
                ->cond('parent_id', $this->MaterialID)
                ->cond('Active', '1')
                ->join('structurematerial')
                ->cond('structurematerial_StructureID', $tableSelector)
                ->order_by('priority');

            // Call user function if exists
            if (is_callable($externalHandler)) {
                // Give it query as parameter
                call_user_func_array($externalHandler, array_merge(array(&$tableQuery), $params));
            }

            // Get table row materials
            $tableMaterialIds = array();
            if ($tableQuery->fields('MaterialID', $tableMaterialIds)) {
                // Create field condition
                $localizationFieldCond = new Condition('or');

                // Create localized condition
                if (sizeof($localizedFields)) {
                    $localizedFieldCond = new Condition('and');
                    $localizedFieldCond->add('materialfield_FieldID', $localizedFields)
                        ->add('materialfield_locale', locale());
                    // Add this condition to condition group
                    $localizationFieldCond->add($localizedFieldCond);
                }

                // Create not localized condition
                if (sizeof($unlocalizedFields)) {
                    $localizationFieldCond->add('materialfield_FieldID', $unlocalizedFields);
                }

                // Create db query
                $materialFieldQuery = dbQuery('materialfield')
                    ->cond('MaterialID', $tableMaterialIds)
                    ->cond($localizationFieldCond);

                // Flip field identifiers as keys
                $tableColumnIds = array_flip($dbTableFieldsIds);
                $resultTable = array_flip($tableMaterialIds);

                /** @var \samson\activerecord\material $dbTableRow Material object (table row) */
                foreach ($materialFieldQuery->exec() as $mf) {
                    if (!is_array($resultTable[$mf['MaterialID']])) {
                        $resultTable[$mf['MaterialID']] = array();
                    }

                    $resultTable[$mf['MaterialID']][$tableColumnIds[$mf->FieldID]] =
                        !empty($mf->Value) ? $mf->Value : (!empty($mf->numeric_value) ? $mf->numeric_value : $mf->key_value);
                }
            }
        }

        return array_values($resultTable);
    }
}
