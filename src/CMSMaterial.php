<?php 
namespace samson\cms;

use samson\activerecord\Condition;
use samson\core\iModuleViewable;
use samson\activerecord\dbRecord;

/**
 * Class for managing CMS material with all related data
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @version 0.9.1
 */
class CMSMaterial extends Material implements iModuleViewable
{
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
}
