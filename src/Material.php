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
    /**
     * Create copy of current object
     * @param null $clone Material for cloning
     * @param array $excludedFields excluded from materialfield fields identifiers
     */
    public function & copy(& $clone = null, $excludedFields = array())
    {
        // If no object is passed - create new instance by clonning
        $clone = !isset($clone) ? clone $this : $clone;

        // Get all related tables data
        $parentWithRelation = dbQuery('\samson\cms\CMSMaterial')
            ->id($this->MaterialID)
            ->join('samson\cms\CMSMaterialField')
            ->join('samson\cms\CMSGallery')
            ->join('samson\cms\CMSNavMaterial')
            ->first();

        // Create structurematerial relations
        foreach ($parentWithRelation->onetomany['_structurematerial'] as $cmsnav) {
            $cmsnav->copy();
        }

        // Create materialfield relaions
        foreach ($parentWithRelation->onetomany['_materialfield'] as $matfield) {
            $materialfield = $matfield->copy();

            // Check if field is ecluded from copying
            if (in_array($materialfield->FieldID, $excludedFields)) {
                $materialfield->Value = '';
                $materialfield->numeric_value = 0;
            } else {
                $materialfield->Value = $matfield->Value;
                $materialfield->numeric_value = $matfield->numeric_value;
            }

            $materialfield->save();
        }

        // If parent has gallery
        if (isset($parentWithRelation->onetomany['_gallery'])) {
            // Iterate all records
            foreach ($parentWithRelation->onetomany['_gallery'] as $cmsgallery) {
                // Copy them
                $cmsgallery->copy();
            }
        }
    }
} 