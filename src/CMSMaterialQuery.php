<?php
namespace samson\cms;

use samson\activerecord\dbRelation;

use samson\activerecord\dbRecord;

use samson\activerecord\dbQuery;

class CMSMaterialQuery extends \samson\activerecord\dbQuery
{	   
	/** @see \samson\activerecord\dbQuery::exec() */
    public function & exec( & $return_value = null)
    {
    	// Perform query handlers
    	$this->_callHandlers();     	
    
    	//$GLOBALS['show_sql'] = true;
    	
    	// Выполним запрос к БД - запишем его в переменную для возврата
		$return_value = db()->find( $this->class_name, $this );		
		
		// Очистим запрос
		$this->flush();		
		
		// Локальная переменная для возврата правильного результата
		$return = null;
		
		// Если хоть что-то передано в функцию - запишем в локальную переменную boolean значение
		// которое покажет результат выполнения запроса к БД
		if( func_num_args() ) $return = (is_array( $return_value ) && sizeof( $return_value ));
		// Сделаем копию полученных данных в локальную переменную
		else $return = & $return_value;		
		
		// Iterate cmsmaterial objects
		foreach ( $return_value as & $db_material ) 
		{
			//trace($db_material);
			
			// Save instance to cache by URL
			dbRecord::$instances[ 'samson\cms\cmsmaterial' ][ $db_material->Url ] = $db_material;
				
			// Pointer to user data
			$db_material->user = $db_material->onetoone['_user'];
				
			// Fill gallery data if we have it
			if( isset($db_material->onetomany['_gallery']) )
			{
				$db_material->gallery = array();
				foreach ( $db_material->onetomany['_gallery'] as $db_gallery ) $db_material->gallery[] = $db_gallery->Src;
			}
		}
		
		// Вернем значение из локальной переменной
		return $return;
    }  
    
    /**
     * Get only published and non-draft CMSMaterials 
     * @return array Published non-draft CMSMaterial collection from DB request 
     */
    public function published( & $return_value = null )
    {
    	// Set published criteria
    	$this->cond( 'Draft', 0 )->cond( 'Published', 1 );
    	
    	// Correctly perform db request for multiple data
		return func_num_args() ? $this->exec( $return_value ) : $this->exec();		 
    }
    
    /**
     * Get only draft CMSMaterials
     * @return array Draft CMSMaterial collection from DB request
     */
    public function drafts( & $return_value = null )
    {
    	// Set draft criteria
    	$this->cond( 'Draft', 0, dbRelation::NOT_EQUAL );
    	 
    	// Correctly perform db request for multiple data
    	return func_num_args() ? $this->exec( $return_value ) : $this->exec();
    }
    
    /**
     * Get only original CMSMaterials without drafts
     * @return array Draft CMSMaterial collection from DB request
     */
    public function originals( & $return_value = null )
    {
    	// Set draft criteria
    	$this->cond( 'Draft', 0, dbRelation::EQUAL );
    
    	// Correctly perform db request for multiple data
    	return func_num_args() ? $this->exec( $return_value ) : $this->exec();
    }

    /** Constructor */
    public function __construct()
    {	
    	parent::__construct( 'samson\cms\cmsmaterial' );
    	
    	// Create db request
    	$this
    		->cond( 'Active', 1 )  
    		//->cond( 'locale', locale())  
    		->join( 'samson\cms\cmsgallery')
    		->join( 'user')
    		->join( 'samson\cms\cmsnavmaterial');
    	//->own_group_by('material.MaterialID');
    }
}