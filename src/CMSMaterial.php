<?php 
namespace samson\cms;

use samson\core\iModuleViewable;
use samson\activerecord\dbConditionArgument;
use samson\activerecord\dbConditionGroup;
use samson\activerecord\dbRecord;

/**
 * Class for managing CMS material with all related data
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @version 0.9.1
 */
class CMSMaterial extends Material implements iModuleViewable
{
    public $class_name = 'material';

    /** Gallery images sorter */
    public static function usortGallery($a, $b)
    {
        return $a->PhotoID > $b->PhotoID;
    }

    /**
	 * Universal method for retrieving material from database with all additional data
	 * such as additional field data with ability to sort, limit, filter by it and gallery data/
	 * 
	 * @param array $field_value 	Array( FIELD_NAME, FIELD_VALUE ) for filtering
	 * @param string $db_cmsnav		Pointer to CMSNav for getting particular materials
	 * @param string $draft			Request "draft" filter
	 * @param string $published		Request "published" filter
	 * @param string $order_by		Request sorting order
	 * @param array $limit			Request limit params
	 * @param string $group_by		Request group by params
	 * @param mixed $handler		External query handler
	 * @param mixed $handler_params	External query handler additional parameters
     * @param array $class_name     ss
	 * @return array CMSMaterial collection by specified request parameters
	 */
    public static function & get(
        array $field_value = null,
        $db_cmsnav = null,
        $draft = null,
        $published = null,
        $order_by = null,
        $limit = null,
        $group_by = null,
        $handler = null,
        $handler_params = array(),
        $class_name = null
    ) {
        $db_materials = array();
        if (!isset($class_name)) {
            $class_name = 'samson\cms\CMSMaterial';
        }
        // Create db request
        $query = dbQuery($class_name)
        ->cond('Active', 1)
        //->cond('locale', locale())
        ->join('samson\cms\CMSGallery')
        ->join('user')
        ->join('samson\cms\CMSNavMaterial')
        //->own_group_by('material.MaterialID')
        ;

        // If we need limiting results
        if (isset($limit)) {
            $query->limit($limit[0], $limit[1]);
        }

        // If we need grouping results
        if (isset($group_by)) {
            $query->group_by($group_by);
        }

        // If request field/value passed
        if (isset($field_value)) {
            $query->cond($field_value[0], $field_value[1]);
        }

        // If we have condifition for drafts
        if (isset($draft)) {
            $query->cond('Draft', $draft);
        }

        // If we have condition for published
        if (isset($published)) {
            $query->cond('Published', $published);
        }

        // If we have ordering condition
        if (is_array($order_by)) {
            $query->order_by($order_by[0], $order_by[1]);

        // Otherwise order by id
        } elseif (!isset($order_by)) {
            $query->order_by('MaterialID', 'DESC');
        }

        // If we have CMSNav filter
        if (isset($db_cmsnav)) {
            $navId = 0;
            if (!is_array($db_cmsnav)) {
                $navId = $db_cmsnav->id;
            } else {
                $navId = $db_cmsnav;
            }
            $query
                ->cond('structurematerial_StructureID', $navId)
                ->cond('structurematerial_Active', 1);
        }

        // if we have handler
        if (is_callable($handler)) {
            // Make first parameter original query object
            array_unshift($handler_params, $query);

            // Make query copy to return for different purposes
            call_user_func_array($handler, $handler_params);
        }
        //$GLOBALS['show_sql'] = true;
        // Perform db request
        if ($query->exec($db_materials)) {
            foreach ($db_materials as & $db_material) {
                //unset($GLOBALS['show_sql']);
                // Save instance to cache by URL
                dbRecord::$instances[ 'samson\cms\CMSMaterial' ][ $db_material->Url ] = $db_material;

                // Pointer to user data
                $db_material->user = $db_material->onetoone['_user'];

                // Fill gallery data
                $db_material->gallery = array();
                if (isset($db_material->onetomany['_gallery'])) {
                    // Sort gallery images
                    usort($db_material->onetomany['_gallery'], '\samson\cms\CMSMaterial::usortGallery');

                    foreach ($db_material->onetomany['_gallery'] as $db_gallery) {
                        $db_material->gallery[] = $db_gallery->Src;
                    }
                }

                if (isset($db_material->onetomany['_structurematerial'])) {
                    $db_material->structure = $db_material->onetomany['_structurematerial'];
                }

                // Remove relation collections
                $db_material->onetoone = array();
                $db_material->onetomany = array();
            }
        }

        return $db_materials;
    }

    /**
     * @param null $clone Material for cloning
     * @param array $excludedFields excluded from materialfield fields identifiers
     */
    public function __cloneMaterial(& $clone = null, $excludedFields = array())
    {
        if (!isset($clone)) {
            $clone = clone $this;
        }

        $parentWithRelation = dbQuery('\samson\cms\CMSMaterial')
                            ->id($this->MaterialID)
                            ->join('samson\cms\CMSMaterialField')
                            ->join('samson\cms\CMSGallery')
                            ->join('samson\cms\CMSNavMaterial')
                    ->first();

        // Create structurematerial relations
        foreach ($parentWithRelation->onetomany['_structurematerial'] as $cmsnav) {
            $structurematerial = new \samson\activerecord\structurematerial(false);
            $structurematerial->MaterialID = $clone->id;
            $structurematerial->StructureID = $cmsnav->StructureID;
            $structurematerial->Active = 1;
            $structurematerial->save();
        }

        // Create materialfield relaions
        foreach ($parentWithRelation->onetomany['_materialfield'] as $matfield) {
            $materialfield = new \samson\activerecord\materialfield(false);
            $materialfield->MaterialID = $clone->id;
            $materialfield->FieldID = $matfield->FieldID;
            if (in_array($materialfield->FieldID, $excludedFields)) {
                $materialfield->Value = '';
            } else {
                $materialfield->Value = $matfield->Value;
            }
            $materialfield->numeric_value = $matfield->numeric_value;
            $materialfield->locale = $matfield->locale;
            $materialfield->Active = $matfield->Active;
            $materialfield->save();
        }

        // Create gallery
        foreach ($parentWithRelation->onetomany['_gallery'] as $cmsgallery) {
            $gallery = new \samson\activerecord\gallery(false);
            $gallery->MaterialID = $clone->id;
            $gallery->Path = $cmsgallery->Path;
            $gallery->Src = $cmsgallery->Src;
            $gallery->Name = $cmsgallery->Name;
            $gallery->Description = $cmsgallery->Description;
            $gallery->Active = $cmsgallery->Active;
            $gallery->save();
        }
    }


    /** Collection of images for material */
    public $gallery;

    /** User who own this material */
    public $user;

    /** @see \samson\core\iModuleViewable::toView() */
    public function toView($key_prefix = '', array $restricted = array())
    {
        // Created restricred cmsmaterial fields collection
        $restricted = array_merge(self::$restricted, $restricted, array('fields'));

        // Default behavior
        $values = parent::toView($key_prefix, $restricted);

        // If editors mode enabled
        if (isset($_SESSION['__CMS_EDITOR__'])) {
            // Iterate throught cmsmaterial fields and all additional fields
            foreach (get_object_vars($this) as $var => $value) {
                // If field not restricted - add to view data collection
                if (!in_array($var, $restricted) && !is_array($value) && !is_object($value)) {
                    $values['__dm__'.$key_prefix.$var] = $this->value($var, true);
                }
            }
        }

        return $values;
    }

	/** Change default dbRecord::save() logic with saving additional field data */
	public function save()
	{
		// Base logic
		parent::save();
		
		// Perform request for additional field metadata
		/*if( dbQuery('materialfield')->join('field')->exec( $db_fields ))
		{
			// Iterate fields and if field metadata has been found
			foreach ( $db_fields as & $db_field ) if( isset( $db_field->onetoone['_field'] ) )
			{
				// Save field value to db					
				$db_field->Value = $this[ $db_field->onetoone['_field']->Name ];			
				$db_field->save();
			}			
		}*/			
		// TODO: Add logic for gallery saving
	}
	
	/** Function for calling unexisting methods */
	public function __call( $name, $arguments )
	{		
		// If no arguments specified consider it is an array
		if( !isset($arguments[0]) ) $arguments = array( false );

		return $this->value( $name, $arguments[0]);		
	}
	
	/**
	 * Universal function for retrieving CMSMaterial field value
	 * but with support of inline editing function 
	 * 
	 * @param string 	$name 			Field name
	 * @param boolean 	$returnValue 	Return or echo value
	 * @return String If $returnValue is true thant returns value
	 */
	public function value( $name, $returnValue = false )
	{
		// If CMSMaterial has such field
		if( isset( $this[ $name ] ) || $this->$name )
		{					
			// Get field value
			$html = $this->$name;
				
			// If we are in editor mode
			if( isset($_SESSION['__CMS_EDITOR__']) )
			{				
				// Render editor value view
				$html = m('cmsapi')
				->set('field',$name)
				->set('id',$this->id)
				->set('value',$this->$name)
				->set('entity','cmsmaterial')
				->output('app/view/editor/material.php');	
			}
			
			// Echo or return
			if( $returnValue === false ) echo $html; 
			else return $html;
		}
	}
	
	/**
	 * Get array of CMSNavigation object for this material
	 * @return array Collection of CMSNav objects
	 */
	public function cmsnavs()
	{	
		// Perfrom DB request to get all connected cmsnavs
		$cmsnavs = array();
		if(dbQuery('samson\cms\CMSNavMaterial')->MaterialID($this->id)->exec($db_nms))
		{
			// Gather CMSNavs object to array
			foreach ($db_nms as $db_nm) $cmsnavs[] = cms()->navigation( $db_nm->StructureID, 'id' );
		}	
		
		return $cmsnavs;
	}
	
	/**
	 * Find all related material to current 
	 * @param function $handler External DB request handler
	 * @return array Collection of related materials
	 */
	public function & related( $handler = null )
	{		
		$db_materials = array();	
		
		//$GLOBALS['show_sql'] = true;	

		// Create DB query 
		$q = dbQuery('samson\cms\cmsrelatedmaterial');
		
		// If external query handler is passed
		if( isset($handler)) $q->handler($handler);
		
		// If we have found related materials
		if( $q->first_material( $this->id )->fields( 'second_material', $ids ) ) 
		{			
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
	public function & relates( $handler = null  )
	{
		$db_materials = array();
	
		//$GLOBALS['show_sql'] = true;		
		
		// Create DB query 
		$q = dbQuery('samson\cms\cmsrelatedmaterial');
		
		// If external query handler is passed
		if( isset($handler)) $q->handler($handler);
		
		// If we have found related materials
		if( $q->second_material( $this->id )->fields( 'first_material', $ids ) )
		{
			// Get related CMSMaterials by ids
			$db_materials = array_merge( $db_materials, cmsquery()->MaterialID($ids)->published());
		}		
	
		return $db_materials;
	}
}