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

    /**
     * Render CMSNav tree as HTML ul>li
     *
     * @param CMSNav    $parent Pointer to parent CMSNav
     * @param string    $view   Path to external view for rendering tree element
     * @param integer   $limit  Maximal depth
     * @param int       $level  Current nesting level
     * @param string    $html   Current
     * @param function  $counterFunc  Callable function
     *
     * @return bool|string
     */
    public function toHTML( & $parent = NULL, $view = NULL, $limit = null, $ulClass = null, $liClass = null, $counterFunc = null, $level = -1, & $html = '' )
    {
        // If no parent passed - consider current CMSNav as parent
        if (!isset( $parent )) {
            $parent = & $this;
        }

        // If nesting limit is specified
        if(($limit > $level) || !isset($limit)) {
            $last = isset($limit)?($limit-$level):0;
            // Get all CMSNav children
            if ($level != -1) {
                if ($parent->base || ($last == 1)){
                    $children = $parent->children();
                } else {
                    $children = $parent->baseChildren();
                }
            } else {
                $children = $parent->baseChildren();
            }


            // If we have children
            if (sizeof($children)) {
                $level++;
                //trace(sizeof($children).' - ');


                // Open container block and pass specified class
                $html .= '<ul class="'.$ulClass.' level-'.$level.'")>';

                // Iterate all CMSNav children
                foreach ( $children as $id => $child )
                {
                    // Open inner container block

                    $currClass = '';

                    // set current
                    if (url()->last == '') {
                        $currClass = 'currentSamsonCMS';
                    }

                    $html .= '<li class="'.$liClass.' '.$currClass.'">';

                    // count how much materials in current structure
                    $counter = 0;
                    if (is_callable($counterFunc)) { $counter = call_user_func($counterFunc, $child); }
                    // If external view is passed - render it
                    if (isset($view)) {
                        $html .= m()->view($view)
                            ->counter($counter)
                            ->cmsmaterial($child)
                            ->output()
                        ;
                    } else { // Only output CMSNav name
                        $html .= $child->Name;
                    }

                    if (!isset($level) || $level < $limit) {
                        // Go deeper into recursion
                        $this->toHTML( $child, $view, $limit, $ulClass, $liClass, null, $level, $html );
                    }

                    // Close inner container block
                    $html .= '</li>';
                }

                // Close container block
                $html .='</ul>';
            }
        }

        return $html;
    }

    /** @deprecated */
    public function isCurrent( $output = '' ){
        if( in_array( url()->text().'/', $this->url_base) ) {
            echo $output; return TRUE;
        }
        return FALSE;
    }
}
