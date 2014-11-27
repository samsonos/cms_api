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
     * @param mixed $clone Material for cloning
     * @param array $excludedFields excluded from materialfield fields identifiers
     * @returns void
     */
    public function & copy(& $clone = null, $excludedFields = array())
    {
        // Create new instance by copying
        $clone = parent::copy($clone);

        // Get all related tables data
        $parentWithRelation = dbQuery('\samson\cms\CMSMaterial')
            ->id($this->MaterialID)
            ->join('samson\cms\CMSMaterialField')
            ->join('samson\cms\CMSGallery')
            ->join('samson\cms\CMSNavMaterial')
            ->first();

        // Create structure material relations
        if (isset($parentWithRelation->onetomany['_structurematerial'])) {
            foreach ($parentWithRelation->onetomany['_structurematerial'] as $cmsNavigation) {
                /** @var \samson\activerecord\Record $cmsNavigation */
                $copy = $cmsNavigation->copy();
                $copy->MaterialID = $clone->id;
                $copy->save();
            }
        }

        // Create material field relations
        if (isset($parentWithRelation->onetomany['_materialfield'])) {
            foreach ($parentWithRelation->onetomany['_materialfield'] as $pMaterialField) {
                /** @var \samson\activerecord\Record $pMaterialField */

                // Check if field is NOT excluded from copying
                if (!in_array($pMaterialField->FieldID, $excludedFields)) {
                    /** @var \samson\activerecord\dbRecord $materialField Copy instance */
                    $copy = $pMaterialField->copy();
                    $copy->MaterialID = $clone->id;
                    $copy->save();
                }
            }
        }

        // If parent has gallery
        if (isset($parentWithRelation->onetomany['_gallery'])) {
            // Iterate all records
            foreach ($parentWithRelation->onetomany['_gallery'] as $cmsGallery) {
                /** @var \samson\activerecord\Record $cmsGallery */
                // Copy them
                $copy = $cmsGallery->copy();
                $copy->MaterialID = $clone->id;
                $copy->save();
            }
        }

        return $clone;
    }
}
