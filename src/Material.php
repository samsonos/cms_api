<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 07.08.14 at 17:11
 */
namespace samson\cms;

/**
 * SamsonCMS Material database record object.
 * This class extends default ActiveRecord material table record functionality.
 * @package samson\cms
 * @author Vitaly Egorov <egorov@samsonos.com>
 */
class Material extends \samson\activerecord\material
{
    /** Override table attributes for late static binding */
    public static $_attributes = array();
    public static $_sql_select = array();
    public static $_sql_from = array();
    public static $_own_group = array();
    public static $_map = array();

    /**
     * Get materials by identifier(s)
     * @param array|string $identifier Material identifier or collection
     * @param array|string $class Class for database query
     * @return \samson\cms\Material[] Collection of found materials
     */
    public static function byId($identifier, $class = 'samson\cms\CMSMaterial')
    {
        // Convert id to array
        $identifier = is_array($identifier) ? $identifier : array($identifier);

        $result = array();

        // If we have passed any identifier
        if (sizeof($identifier)) {
            // Perform db request and get materials
            $result = dbQuery($class)
                ->cond('MaterialID', $identifier)
                ->exec();
        }

        return $result;
    }

    /**
     * Get select additional field text value
     * @return string Select field text
     */
    public function selectText($fieldID)
    {
        /** @var \samson\activerecord\field $field */
        $field = null;
        if (dbQuery('field')->id($fieldID)->first($field)) {
            // If this entity has this field set
            if (isset($this[$field->Name]{0})) {
                $types = array();
                foreach (explode(',', $field->Value) as $typeValue) {
                    $typeValue = explode(':', $typeValue);
                    $types[$typeValue[0]] = $typeValue[1];
                }
                return $types[$this[$field->Name]];
            }
        }

        // Value not set
        return '';
    }

    /**
     * Create copy of current object
     * @param mixed $clone Material for cloning
     * @param array $excludedFields excluded from materialfield fields identifiers
     * @returns void
     */
    public function & copy(& $clone = null, $excludedFields = array())
    {
        // Create new instance by copying
        $clone = parent::copy($clone);

        /** @var \samson\activerecord\structurematerial[] $objects Create structure material relations */
        $objects = array();
        if (dbQuery('structurematerial')->cond('MaterialID', $this->MaterialID)->exec($objects)) {
            foreach ($objects as $cmsNavigation) {
                /** @var \samson\activerecord\Record $copy */
                $copy = $cmsNavigation->copy();
                $copy->MaterialID = $clone->id;
                $copy->save();
            }
        }
        /** @var \samson\activerecord\materialfield[] $objects Create material field relations */
        $objects = array();
        if (dbQuery('materialfield')->cond('MaterialID', $this->MaterialID)->exec($objects)) {
            foreach ($objects as $pMaterialField) {
                // Check if field is NOT excluded from copying
                if (!in_array($pMaterialField->FieldID, $excludedFields)) {
                    /** @var \samson\activerecord\dbRecord $copy Copy instance */
                    $copy = $pMaterialField->copy();
                    $copy->MaterialID = $clone->id;
                    $copy->save();
                }
            }
        }

        /** @var \samson\activerecord\gallery[] $objects Create gallery field relations */
        $objects = array();
        if (dbQuery('gallery')->cond('MaterialID', $this->MaterialID)->exec($objects)) {
            foreach ($objects as $cmsGallery) {
                /** @var \samson\activerecord\Record $copy */
                $copy = $cmsGallery->copy();
                $copy->MaterialID = $clone->id;
                $copy->save();
            }
        }

        return $clone;
    }
}
