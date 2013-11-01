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
	
	
	protected $parents = array();
	
	
	public $children = array();
	
	
	protected $url_base = '';
	
	
	protected $level = 0;
	
	
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
	
	
	public function parents( CMSNav & $bound = NULL )
	{		
		$parents = array();
		
		for ($i = 0; $i < sizeof( $this->parents ); $i++ )
		{
			$parents[] = & $this->parents[ $i ];
			
			if( isset( $bound ) && ( $bound == $this->parents[ $i ] ) ) break;
		}		
	
		return array_reverse( $parents );
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
	
	
	public function toHTML( CMSNav & $parent = NULL, & $html = '', $view = NULL, $level = 0 )
	{		
		if( ! isset( $parent ) ) $parent = & $this;

		$children_count = sizeof($parent->children);	

		//trace($level.'-'.$parent->Name);
		
		//if($level > 10) return $html;

		if( $children_count )
		{
			$html .= '<ul>';
			
			foreach ( $parent->children as $id => $child ) 		
			{				
				if( isset( $view ) ) $html .= '<li>'.s()->render( $view, array( 'db_structure' => $child ) ).'';
				else $html .= '<li>'.$child->Name.'</li>';
	
				$this->toHTML( $child, $html, $view, $level++ );

				$html .= '</li>';
			}
	
			$html .='</ul>';
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
	
	public function isCurrent( $output = '' ){ if( in_array( url()->text().'/', $this->url_base) ) { echo $output; return TRUE; } return FALSE; }	
	
	
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

	/** Serialize handler */
	public function __sleep()
	{		
		eval('$_attributes = '.get_class($this).'::$_attributes;');		
		
		return $_attributes; 
	}
	public function rewind(){reset( $this->children );	}
	public function next(){	return next( $this->children );	}
	public function current(){	return current( $this->children );	}
	public function key(){	return key( $this->children );	}
	public function valid(){$key = key( $this->children );	return ( $key !== NULL && $key !== FALSE );	}	
}

dbRecord::$instances[ "samson\cms\cmsnav" ] = array();