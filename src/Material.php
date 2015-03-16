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
     * Get material entities by identifier(s).
     * @param array|string $identifier Material identifier or their collection
     * @param self[]|array|null $return Variable where request result would be returned
     * @return bool|self[] True if material entities has been found
     */
    public static function byId($identifier, & $return = array())
    {
        // Perform db request and get materials
        if (dbQuery(get_called_class())
                ->cond('MaterialID', $identifier)
                ->exec($return)) {
            // If only one argument is passed - return query result, otherwise bool
            return func_num_args() > 1 ? true : $return;
        }

        // If only one argument is passed - return empty array, otherwise bool
        return func_num_args() > 1 ? false : array();
    }

    /**
     * Get material entities by url(s).
     * @param array|string $url Material URL or their collection
     * @param self[]|array|null $return Variable where request result would be returned
     * @return bool|self[] True if material entities has been found
     */
    public static function byUrl($url, & $return = array())
    {
        // Perform db request and get materials
        if (dbQuery(get_called_class())
            ->cond('Url', $url)
            ->exec($return)) {
            // If only one argument is passed - return query result, otherwise bool
            return func_num_args() > 1 ? true : $return;
        }

        // If only one argument is passed - return empty array, otherwise bool
        return func_num_args() > 1 ? false : array();
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
     * Get collection of images for material by gallery additional field selector
     * @param string $fieldSelector Additional field selector value
     * @param string $selector Additional field field name to search for
     * @return \samson\activerecord\gallery[] Collection of images in this gallery additional field for material
     */
    public function & gallery($fieldSelector, $selector = 'FieldID')
    {
        /** @var \samson\activerecord\gallery[] $images Get material images for this gallery */
        $images = array();

        /* @var \samson\activerecord\field Get field object if we need to search it by other fields */
        $field = null;
        if ($selector != 'FieldID') {
            $field = dbQuery('field')->cond($selector, $fieldSelector)->first();
            $fieldSelector = $field->id;
        }

        /** @var \samson\activerecord\materialfield $dbMaterialField Find material field gallery record */
        $dbMaterialField = null;
        if (dbQuery('materialfield')
            ->cond("FieldID", $fieldSelector)
            ->cond('MaterialID', $this->id)
            ->first($dbMaterialField)
        ) {
            // Get material images for this materialfield
            if (dbQuery('gallery')->cond('materialFieldId', $dbMaterialField->id)->exec($images)) {

            }
        }

        return $images;
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
