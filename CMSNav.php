<?php 
namespace samson\cms;

use samson\activerecord\dbRecord;
use samson\activerecord\idbLocalizable;
use samson\activerecord\structure;

class CMSNav extends structure implements  \Iterator, idbLocalizable
{			
	public static function build( CMSNav & $parent, array & $records, $level = 0 )
	{			
		foreach ($records as & $record )  
		{		
			if( $record->StructureID == $parent->StructureID ) continue;

			if( $record->ParentID == $parent->StructureID )
			{
				$record->parent = & $parent;
	
				$current = & $record;
	
				$url_base = '';
	
				while( isset( $current ) )
				{
					$record->parents[] = & $current;
	
					$url_base = trim($current->Url.'/'.$url_base);

					$record->url_base[ $current->StructureID ] = $url_base;
					$record->url_base[ $current->StructureID.'_'.locale() ] = locale().'/'.$url_base;
	
					$current = & $current->parent;
				}
				
				$record->level = $level;								

				$parent->children[ 'id_'.$record->StructureID ] = $record;			

				self::build( $record, $records, ( $level + 1 ) );
			}
		}
	}

	
	public static $top;

	public $parent = NULL;

    public $parentsnav = array();
	
	
	protected $parents = array();
	
	
	public $children = array();
	
	
	protected $url_base = '';
	
	
	protected $level = 0;

    protected $base = false;

    public function tree(){
        $tree = array();
        $s_rs = array();
        //Get all structure telations
        if(dbQuery('structure_relation')->exec($s_r)){

        }
    }
	
	
	public function toView( $key_prefix = '', array $restricted = array() )
	{
		return parent::toView( $key_prefix, array( 'parent','parents','children', ) );
	}
	
	
	/**
	 * Find materials for this CMSNav
	 * @param array $order_by Sorting order of materials
	 * @return array Collection of CMSMaterial
	 */
	public function materials( array $order_by = NULL ){ return CMSMaterial::get( NULL, $this, 0, 1, $order_by ); }
	
	/**
	 * Find $count random material for this CMSNav  
	 * @param number $count Materials count
	 * @return array Collection of CMSMaterial
	 */
	public function random_materials( $count = 1 ){ return CMSMaterial::get( NULL, $this, 0, 1, array( 'RAND()', ''), array( 0, $count) ); }
	
	
	public function fields()
	{
		$fields = array();		
		
		if( dbQuery('samson\cms\cmsnavfield')->StructureID($this->id)->Active(1)->exec( $db_fields ) )
		{
			$id = array();
			foreach ( $db_fields as $db_field ) $id[] = $db_field->FieldID;			
	
			$fields = dbQuery('field')->FieldID( $id )->exec();		
		}	
		
		return $fields;
	}
	
	
	public function url( CMSNav & $parent = NULL )
	{
		if( ! isset($parent) ) $parent = & $this;
		
		echo (isset($this->url_base[ $parent->id ]) ? $this->url_base[ $parent->id ]:'');
	}

    public function parents( CMSNav & $bound = NULL)
	{
        $parents = array();
        $this->base();
        if (sizeof($this->parentsnav)>0) {
            $parent = current($this->parentsnav);
            $parents[] = $parent;
            if( !(isset( $bound ) && ( $bound == $this->parentsnav[0] )) ){
                $parents = array_merge($parents, $parent->parents($bound));
            }
        }

		//return array_reverse( $parents );
        return $parents;
	}

    public function children()
    {
        // check? is this objeck full;
        $this->base();
        return $this->children;
    }

    public function parent()
    {
        // check? is this objeck full;
        $this->base();
        return $this->parent;
    }
	
	
	public function priority( $direction = NULL )
	{
				if( isset($this->parent) )
		{
						$p_index = 0;
		
						foreach ( $this->parent as $id => $child )
			{
								if( $child->PriorityNumber != $p_index)
				{
										$child->PriorityNumber = $p_index;
						
										$child->save();
				}
					
								$p_index++;
			}	

						$children = array_values( $this->parent->children );
			
						$old_index = $this->PriorityNumber;
			
						$new_index =  $old_index - $direction;
						$new_index = $new_index == sizeof($children) ? 0 : $new_index;
						$new_index = $new_index == -1 ? sizeof($children) - 1 : $new_index;
			
						if( isset($children[ $new_index ] ) )
			{
								$second_nav = $children[ $new_index ];
								$second_nav->PriorityNumber = $old_index;
								$second_nav->save();
					
								$this->PriorityNumber = $new_index;
								$this->save();
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
	
	
	public function & def()
	{
				$db_cmsmat = null;
		
				if( ! ifcmsmat( $this->MaterialID, $db_cmsmat, 'id') )
		{
					}
		
				return $db_cmsmat;
	}	
	
	public function isCurrent( $output = '' ){
        if( in_array( url()->text().'/', $this->url_base) ) {
            echo $output; return TRUE;
        }
        return FALSE;
    }
	
	
	public function __call( $name, $arguments )
	{			
						if( isset( $this[ $name ] ) || $this->$name ) 
		{
						$html = $this->$name;
			
						if( isset($_SESSION['__CMS_EDITOR__']) ) 
				$html = m('cmsapi')
				->set('field',$name)
				->set('id',$this->id)
				->set('value',$this->$name)
				->set('entity','cmsnav')
			->output('app/view/editor/material.php');
			
						echo $html;
		}
	}
    public function prepare()
    {
        $this->base = true;

        if (isset($this->onetomany['_children'])) {
            foreach ($this->onetomany['_children'] as & $child) {
                $this->children[$child->id] = & $child;
            }
            unset($this->onetomany['_children']);
        }

        if (isset($this->onetomany['_parents'])) {
            foreach ($this->onetomany['_parents'] as & $parent) {
                $this->parentsnav[$parent->id] = & $parent;
                $this->parent = & $parent;
            }
            unset($this->onetomany['_parents']);
        }
    }

    /*
     * Has object all its relations?
     * If not, fill relations.
     */
    protected function base()
    {
        if (!$this->base){
            //$classname = ns_classname('cmsnav', 'samson\cms');
            $classname = get_class($this);
            $cmsnav = null;
            if( dbQuery($classname)
                ->cond('Active',1)
                ->StructureID( $this->id)
                ->join('children_relations',null, true)
                ->join('children', get_class($this))
                ->join('parents_relations', null, true)
                ->join('parents', get_class($this))
                ->first( $cmsnav )) {

                if (isset($cmsnav->onetomany['_children'])) {
                    $this->onetomany['_children'] = & $cmsnav->onetomany['_children'];
                }

                if (isset($cmsnav->onetomany['_parents'])) {
                    $this->onetomany['_parents'] = & $cmsnav->onetomany['_parents'];
                }

                $this->prepare();
            }
        }
    }

    protected function baseChildren()
    {
        //elapsed('startBaseChildren');
        //trace('baseChildren');
        $this->base();
        //$classname = ns_classname('cmsnav', 'samson\cms');
        $classname = get_class($this);
        //trace($classname);
        $cmsnavs = null;
        $children_id = array_keys($this->children);
        //elapsed('queryStart');
        if (sizeof($children_id)){
            if( dbQuery($classname)
                ->cond('Active',1)
                ->cond('StructureID', $children_id)
                ->join('children_relations', null, true)
                ->join('children', $classname)
                ->join('parents_relations', null, true)
                ->join('parents', $classname)
                ->exec( $cmsnavs )) {
                //elapsed('queryEnd');
                $this->children = array();
                foreach ($cmsnavs as & $cmsnav) {
                    $cmsnav->prepare();
                    $this->children[] = & $cmsnav;
                }
            }
        }
        //elapsed('endBaseChildren');
        return$this->children;
    }

	/** Serialize handler */
	public function __sleep()
	{
        $_attributes = null;
		eval('$_attributes = '.get_class($this).'::$_attributes;');		
		
		return $_attributes; 
	}
	public function rewind(){$this->base(); reset( $this->children );	}
	public function next(){$this->base();	return next( $this->children );	}
	public function current(){$this->base(); return current( $this->children );	}
	public function key(){$this->base();return key( $this->children );	}
	public function valid(){$this->base(); $key = key( $this->children );	return ( $key !== NULL && $key !== FALSE );	}
}

// dbRecord::$instances[ "samson\cms\cmsnav" ] = array();