<?php 
namespace samson\cms;

/**
 * Class CMSNav
 * @package samson\cms
 * @deprecated @see Navigation
 */
class CMSNav extends Navigation
{
    public static function build(CMSNav & $parent, array & $records, $level = 0)
    {
        // Iterate all items on current level
        foreach ($records as & $record) {
            // If if item is current level parent item TODO: How could it be?
            if ($record->StructureID == $parent->StructureID) {
                continue;
            }

            // If this item is connected with current level parent item
            if ($record->ParentID == $parent->StructureID) {
                // Save pointer to parent item
                $record->parent = & $parent;

                $current = & $record;

                $url_base = '';

                while (isset($current)) {
                    $record->parents[] = & $current;

                    $url_base = trim($current->Url.'/'.$url_base);

                    $record->url_base[ $current->StructureID ] = $url_base;
                    $record->url_base[ $current->StructureID.'_'.locale() ] = locale().'/'.$url_base;

                    $current = & $current->parent;
                }

                $record->level = $level;

                $parent->children['id_'.$record->StructureID] = $record;

                // Go deeper in recursion
                self::build($record, $records, ($level + 1));
            }
        }
    }
}
