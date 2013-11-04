<?php 
namespace samson\cms;

use samson\activerecord\TableRelation;

use samson\activerecord\CacheTable;
use samson\activerecord\material;
use samson\core\CompressableService;
use samson\activerecord\dbRecord;

class CMS extends CompressableService
{	
	public $authed = true;
	
	/** Identifier */
	protected $id = 'cmsapi';
	
	/** Requirenments */
	protected $requirements = array
	(
		'ActiveRecord',
		'md5'
	);
	
	/** Collection of material additional fields */
	public $material_fields = array();
	
	/**
	 * @see ModuleConnector::prepare()
	*/
	public function prepare()
	{
		// SQL команда на добавление таблицы пользователей
		$sql_user = "CREATE TABLE IF NOT EXISTS `user` (
		  `UserID` int(11) NOT NULL AUTO_INCREMENT,
		  `FName` varchar(255) NOT NULL,
		  `SName` varchar(255) NOT NULL,
		  `TName` varchar(255) NOT NULL,
		  `Email` varchar(255) NOT NULL,
		  `Password` varchar(255) NOT NULL,
		  `md5_Email` varchar(255) NOT NULL,
		  `md5_Password` varchar(255) NOT NULL,
		  `Created` datetime NOT NULL,
		  `Modyfied` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `GroupID` int(11) NOT NULL,
		  `Active` int(11) NOT NULL,
		  `Online` int(11) NOT NULL,
		  `LastLogin` datetime NOT NULL,
		  PRIMARY KEY (`UserID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
		
		// SQL команда на добавление таблицы пользователей
		$sql_gallery = "CREATE TABLE IF NOT EXISTS `gallery` (
		  `PhotoID` int(11) NOT NULL AUTO_INCREMENT,
		  `MaterialID` int(11) NOT NULL,
		  `Path` varchar(255) NOT NULL,
		  `Src` varchar(255) NOT NULL,
		  `Thumbpath` varchar(255) NOT NULL,
		  `Thumbsrc` varchar(255) NOT NULL,
		  `Loaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Description` text NOT NULL,
		  `Name` varchar(255) NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`PhotoID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";	
			
		// SQL команда на добавление таблицы групп пользователей
		$sql_group = "CREATE TABLE IF NOT EXISTS `group` (
		  `GroupID` int(20) NOT NULL AUTO_INCREMENT,
		  `Name` varchar(255) NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`GroupID`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
	
		// SQL команда на добавление таблицы связей пользователей и групп
		$sql_groupright ="CREATE TABLE IF NOT EXISTS `groupright` (
		  `GroupRightID` int(11) NOT NULL AUTO_INCREMENT,
		  `GroupID` int(10) NOT NULL,
		  `RightID` int(20) NOT NULL,
		  `Entity` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '_',
		  `Key` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		  `Ban` int(10) NOT NULL,
		  `TS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`GroupRightID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;";
	
		// SQL команда на добавление таблицы прав пользователей
		$sql_right = "CREATE TABLE IF NOT EXISTS `right` (
		  `RightID` int(20) NOT NULL AUTO_INCREMENT,
		  `Name` varchar(255) NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`RightID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
	
		// Related materials
		$sql_relation_material = "CREATE TABLE IF NOT EXISTS `related_materials` (
		  `related_materials_id` int(11) NOT NULL AUTO_INCREMENT,
		  `first_material` int(11) NOT NULL,
		  `first_locale` varchar(10) NOT NULL,
		  `second_material` int(11) NOT NULL,
		  `second_locale` varchar(10) NOT NULL,
		  PRIMARY KEY (`related_materials_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";		
	
		// SQL команда на добавление таблицы материалов
		$sql_material = "CREATE TABLE IF NOT EXISTS `material` (
		  `MaterialID` int(11) NOT NULL AUTO_INCREMENT,
		  `Name` varchar(555) NOT NULL,
		  `Content` text NOT NULL,
		  `Published` int(11) NOT NULL,
		  `Created` datetime NOT NULL,
		  `UserID` int(11) NOT NULL,
		  `Modyfied` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Teaser` text NOT NULL,
		  `Url` varchar(255) NOT NULL,
		  `Keywords` varchar(255) NOT NULL,
		  `Description` varchar(255) NOT NULL,
		  `Title` varchar(255) NOT NULL,
		  `Draft` int(11) NOT NULL,
		  `Draftmaterial` int(11) NOT NULL,
		  `Active` int(11) NOT NULL DEFAULT '1',
		PRIMARY KEY (`MaterialID`),
		KEY `Url` (`Url`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
			
		// SQL команда на добавление таблицы навигации
		$sql_structure = "CREATE TABLE IF NOT EXISTS `structure` (
		  `StructureID` int(11) NOT NULL AUTO_INCREMENT,
		  `ParentID` int(11) NOT NULL,
		  `Name` varchar(255) NOT NULL,
		  `Created` datetime NOT NULL,
		  `UserID` int(11) NOT NULL,
		  `Modyfied` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Url` varchar(255) NOT NULL,
		  `MaterialID` int(11) NOT NULL,
		  `PriorityNumber` int(11) NOT NULL,
		  `Active` int(11) NOT NULL DEFAULT '1',
		PRIMARY KEY (`StructureID`),
		KEY `Url` (`Url`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
			
		// SQL комманда на создание таблицы связей навигации и материалов
		$sql_structurematerial = "CREATE TABLE IF NOT EXISTS `structurematerial` (
		  `StructureMaterialID` int(11) NOT NULL AUTO_INCREMENT,
		  `StructureID` int(11) NOT NULL,
		  `MaterialID` int(11) NOT NULL,
		  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Active` int(11) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`StructureMaterialID`),
		  KEY `StructureID` (`StructureID`),
		  KEY `MaterialID` (`MaterialID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

		// SQL команда на добавление таблицы полей
		$sql_field = "CREATE TABLE IF NOT EXISTS `field` (
		  `FieldID` int(11) NOT NULL AUTO_INCREMENT,
		  `ParentID` int(11) NOT NULL,
		  `Name` varchar(255) NOT NULL,
		  `Type` int(11) NOT NULL,
		  `Value` text NOT NULL,
		  `Description` text NOT NULL,
		  `UserID` int(11) NOT NULL,
		  `Created` datetime NOT NULL,
		  `Modyfied` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`FieldID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

		// SQL команда на добавление таблицы связей ЄНС с полями
		$sql_navfield = "CREATE TABLE IF NOT EXISTS `structurefield` (
		  `StructureFieldID` int(11) NOT NULL AUTO_INCREMENT,
		  `StructureID` int(11) NOT NULL,
		  `FieldID` int(11) NOT NULL,
		  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`StructureFieldID`),
		  KEY `StructureID` (`StructureID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

		// SQL комманда на создание таблицы связей материалов и полей
		$sql_materialfield = "CREATE TABLE IF NOT EXISTS `materialfield` (
		  `MaterialFieldID` int(11) NOT NULL AUTO_INCREMENT,
		  `FieldID` int(11) NOT NULL,
		  `MaterialID` int(11) NOT NULL,
		  `Value` text NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`MaterialFieldID`),
		  KEY `MaterialID` (`MaterialID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
		
		// SQL table for storing database version
		$sql_version = " CREATE TABLE IF NOT EXISTS `cms_version` ( `version` varchar(15) not null default '1')
				ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

		// Выполним SQL комманды
		db()->simple_query($sql_version);
		db()->simple_query($sql_field);
		db()->simple_query($sql_navfield);
		db()->simple_query($sql_materialfield);
		db()->simple_query($sql_material);
		db()->simple_query($sql_structure);
		db()->simple_query($sql_structurematerial);
		db()->simple_query($sql_user);
		db()->simple_query($sql_group);
		db()->simple_query($sql_right);
		db()->simple_query($sql_groupright);
		db()->simple_query($sql_relation_material);
		db()->simple_query($sql_gallery);
		db()->simple_query("INSERT INTO `user` (`UserID`, `FName`, `SName`, `TName`, `Email`, `Password`, `md5_Email`, `md5_Password`, `Created`, `Modyfied`, `GroupID`, `Active`, `Online`, `LastLogin`) VALUES
	 (1, 'Виталий', 'Егоров', 'Игоревич', 'admin', 'vovan123', '21232f297a57a5a743894a0e4a801fc3', 'fa9bb23b40db7ccff9ccfafdac0f647c', '2011-10-25 14:59:06', '2013-05-22 11:52:38', 1, 1, 1, '2013-05-22 14:52:38')
			ON DUPLICATE KEY UPDATE Active=1");
		
		// Initiate migration mechanism
		db()->migration( get_class($this), array( $this, 'migrator' ));
		
		// Define permanent table relations
		new TableRelation( 'material', 'user', 'UserID' );
		new TableRelation( 'material', 'gallery', 'MaterialID', TableRelation::T_ONE_TO_MANY );
		new TableRelation( 'material', 'materialfield', 'MaterialID', TableRelation::T_ONE_TO_MANY );
		new TableRelation( 'material', 'field', 'materialfield.FieldID', TableRelation::T_ONE_TO_MANY );
		new TableRelation( 'material', 'structurematerial', 'MaterialID', TableRelation::T_ONE_TO_MANY );
		new TableRelation( 'material', 'structure', 'structurematerial.StructureID', TableRelation::T_ONE_TO_MANY );
		new TableRelation( 'materialfield', 'field', 'FieldID' );
		new TableRelation( 'materialfield', 'material', 'MaterialID' );
		new TableRelation( 'structurematerial', 'structure', 'StructureID' );
		new TableRelation( 'structurematerial', 'materialfield', 'MaterialID', TableRelation::T_ONE_TO_MANY  );		
		new TableRelation( 'structure', 'material', 'MaterialID' );
		new TableRelation( 'structure', 'user', 'UserID' );
		new TableRelation( 'related_materials', 'material', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID' );
		new TableRelation( 'related_materials', 'materialfield', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID' );
		new TableRelation( 'field', 'structurefield', 'FieldID' );
		new TableRelation( 'field', 'structure', 'structurefield.StructureID' );		
		new TableRelation( 'structurefield', 'field', 'FieldID'  );
		new TableRelation( 'structurefield', 'materialfield', 'FieldID'  );		
		new TableRelation( 'structurefield', 'material', 'materialfield.MaterialID'  );				
	
		//elapsed('CMS:prepare');
		
		// Все прошло успешно
		return true && parent::prepare(); 
	}
	
	/**
	 * Handler for CMSAPI database version manipulating
	 * @param string $to_version Version to switch to
	 * @return string Current database version
	 */
	public function migrator( $to_version = null )
	{
		// If something passed - change database version to it
		if( func_num_args() ) 
		{
			// Save current version to special db table
			db()->simple_query("ALTER TABLE  `cms_version` CHANGE  `version`  `version` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '".$to_version."';");
		}
		// Return current database version
		else 
		{
			$version_row = db()->query('SHOW COLUMNS FROM `cms_version`');
			return $version_row[0]['Default'];
		}
	}
	
	/** Automatic migration to new CMS table structure */
	public function migrate_1_to_2()
	{		
		elapsed('Removing `Relations` table');
		db()->simple_query('DROP TABLE relations');
		
		elapsed('Adding `numeric_value` field into `materialfield` table');
		db()->simple_query('ALTER TABLE  `materialfield` ADD  `numeric_value` INT( 255 ) NOT NULL AFTER  `Value`');
		
		elapsed('Adding `locale` field into `material` table');
		db()->simple_query('ALTER TABLE  `material` ADD  `locale` varchar( 2 ) NOT NULL AFTER `Name`');	
		
		elapsed('Removing `Draftmaterial` field from `material` table');
		db()->simple_query('ALTER TABLE `material` DROP `Draftmaterial`');
		
		/*
		
		// Create additional fields to move
		$fields = array('Content','Teaser','Keywords','Description','Title');
		$ids = array();
		foreach ( $fields as $f) 
		{
			$field = new \samson\activerecord\field( false );
			$field->Name = $f;
			$field->save();
			
			// Save field id
			$ids[ $f ] = $field->id;
		}
		
		// Iterate existing materials and create material field
		if( dbQuery('material')->exec( $db_materials ) ) foreach ( $db_materials as $db_material )
		{
			foreach ( $ids as $f => $fid )
			{
				// Create materialfield entry
				$mf = new \samson\activerecord\materialfield( false );
				$mf->MaterialID = $db_material->id;
				$mf->FieldID = $fid;
				$mf->Value = $db_material->$f;
				$mf->save();
			}
		}		
		*/
		/*
		elapsed('Removing data fields from `material` table');
		db()->simple_query('ALTER TABLE `material` DROP `Teaser`');		
		db()->simple_query('ALTER TABLE `material` DROP `Keywords`');		
		db()->simple_query('ALTER TABLE `material` DROP `Description`');
		db()->simple_query('ALTER TABLE `material` DROP `Content`');
		db()->simple_query('ALTER TABLE `material` DROP `Title`');
		*/
		
		elapsed('Changing `material` table columns order');
		db()->simple_query('ALTER TABLE `material` MODIFY `Teaser` TEXT AFTER `Content`');
		db()->simple_query('ALTER TABLE `material` MODIFY `Published` INT(1) UNSIGNED AFTER `Draft`');
		db()->simple_query('ALTER TABLE `material` MODIFY `Active` INT(1) UNSIGNED AFTER `Published`');
		db()->simple_query('ALTER TABLE `material` MODIFY `UserID` INT(11) AFTER `Title`');
		db()->simple_query('ALTER TABLE `material` MODIFY `Modyfied` TIMESTAMP AFTER `Title`');		
		db()->simple_query('ALTER TABLE `material` MODIFY `Created` DATETIME AFTER `Title`');
	}
	
	/** Automatic migration to new CMS table structure */
	public function migrate_2_to_3()
	{	
		elapsed('Adding `locale` field into `structure` table');
		db()->simple_query('ALTER TABLE  `structure` ADD  `locale` VARCHAR( 10 ) NOT NULL AFTER  `Name` ;');
	}
	
	/** Automatic migration to new CMS table structure */
	public function migrate_3_to_4()
	{
		elapsed('Adding `locale` field into `materialfield` table');
		db()->simple_query('ALTER TABLE  `materialfield` ADD  `locale` VARCHAR( 10 ) NOT NULL AFTER  `numeric_value` ;');
		elapsed('Adding `local` field into `field` table');
		db()->simple_query('ALTER TABLE  `field` ADD  `local` int( 10 ) NOT NULL AFTER  `Type` ;');
	}
		
	
	
	
	/**
	 * Get CMSMaterial by selector 
	 * Field to search for can be specified, by default search if performed by URL field
	 * Function supports finding material by own fields and by any additional fields
	 * 
	 * @param string $selector  Value of CMSMaterial to search
	 * @param string $field		Field name for searching
	 * @return CMSMaterial Instance of CMSMaterial on successfull search
	 */
	public function & material( $selector, $field = 'Url' )
	{			
		$db_cmsmat = null;	
	
		// If id passed switch to real table column name
		if( $field == 'id' ) $field = 'MaterialID';
		
		// Build classname with PHP < 5.3 compatibility
		$classname = ns_classname('cmsmaterial', 'samson\cms');
		
		// If instance of CMSMaterial passed - just return it
		if( is_a( $selector, $classname )) return $selector;		
		// Try to search activerecord instances cache by selector 
		else if( isset( dbRecord::$instances[$classname][$selector] )) $db_cmsmat = & dbRecord::$instances[$classname][$selector];
		// Try to load from memory cache
		//else if( CacheTable::ifget( $selector, $db_cmsmat ) );
		// Perform request to database 	 
		else
		{		
			// Get material	by field		
			$db_cmsmat = CMSMaterial::get( array( $field, $selector ), NULL, 0, 1 );	

			// If we have found material - get the first one 
			if( is_array( $db_cmsmat ) && sizeof( $db_cmsmat ) ) $db_cmsmat = array_shift($db_cmsmat);
			else $db_cmsmat = null;
		}
			
		return $db_cmsmat;
	}
	
	/**
	 * Get CMSNav by selector
	 * Field to search for can be specified, by default search if performed by URL field
	 * 
	 * @param string $selector
	 * @param string $field
	 * @return string|NULL
	 */
	public function & navigation( $selector, $field = 'Url' )
	{	
		$cmsnav = null;
		
		// If no selector passed
		if( !isset($selector) ) return $cmsnav;	
		
		// If id passed switch to real table column name
		if( $field == 'id' ) $field = 'StructureID';
		
		// Build classname with PHP < 5.3 compatibility
		$classname = ns_classname('cmsnav', 'samson\cms');
		
		// If instance of CMSNav passed - just return it
		if( is_a( $selector, $classname)) return $selector;	
		// Try to search activerecord instances cache by selector
		else if( isset(dbRecord::$instances[$classname][$selector]) ) {$cmsnav = & dbRecord::$instances[$classname][$selector];}		
		// Perform request to database
		else if( dbQuery($classname)->cond('Active',1)->cond( $field, $selector )->first( $cmsnav ));			
		
		return $cmsnav;
	}	
	
	/**
	 * Perform request to get CNSMaterials by CMSNav
	 * @param mixed $selector 	CMSNav selector
	 * @param string $field		CMSNav field name for searching
	 * @param string $handler	External handler
	 */
	public function & navmaterials( $selector, $field = 'Url', $handler = null )
	{
		$result = array();
	
		// Find CMSNav
		if( null !== ($db_nav = $this->navigation( $selector, $field )) )
		{		
			// Get material ids from structure materials records 
			$ids = array();		
			if(dbQuery('samson\cms\cmsnavmaterial')->cond( 'StructureID', $db_nav->id )->fields( 'MaterialID', $ids ))
			{	
				// Create material db query
				$q = cmsquery()->id($ids);
				
				// Set ecternal query handler
				if( isset( $handler ) ) $q->handler( $handler ); 
				
				// Perform DB request and get materials
				$result = $q->exec();				
			}
		}		

		return $result;
	}
	
	public function e404()
	{
		$selector = url()->last();	
		
		s()->active( $this );
		
		if( ifcmsmat( $selector, $db_material )) 
		{				
			$this->title = $db_material->Name;
			$this->keywords = $db_material->Keywords;
			$this->description = $db_material->Description;	

			$this->set( $db_material );
		
			cmsapi_template();
		}		
		else cmsapi_e404();
	}	
	
	public function buildNavigation()
	{	
		dbRecord::$instances['samson\cms\cmsnav'] = array();
		
		CMSNav::$top = new CMSNav( false );
		CMSNav::$top->Name = 'Корень навигации';
		CMSNav::$top->Url = 'NAVIGATION_BASE';
		CMSNav::$top->StructureID = 0;
		// Try load navigation cache from memmory cache
		//if( ! CacheTable::ifget('cms_navigation_cache', dbRecord::$instances['samson\cms\cmsnav'] ) )
		{			
			// Perform request to db
			$cmsnavs = dbQuery('samson\cms\cmsnav')->cond('Active',1)
			//->cond('locale', locale())
			->order_by('PriorityNumber','asc')->exec();			
			
			foreach ( $cmsnavs as $cmsnav ) 
			{
				dbRecord::$instances['samson\cms\cmsnav'][ $cmsnav->Url ] = $cmsnav;		
			}
			
			// Save all array to memmory cache
			//CacheTable::set( 'cms_navigation_cache', dbRecord::$instances['samson\cms\cmsnav'] );				
		}			
		
		//trace(dbRecord::$instances['samson\cms\cmsnav']);
				
		// Build navigation tree
		CMSNav::build( CMSNav::$top, dbRecord::$instances['samson\cms\cmsnav'] );
		
		//trace($cmsnavs);
	}	
	
	/** @see \samson\core\CompressableExternalModule::afterCompress() */
	public function afterCompress( & $obj = null, array & $code = null )
	{
		// Fill additional fields data to material db request data for automatic altering material request		
		$select_data = array();		
		
		$t_name = '_mf';		
		
		// Perform db query to get all possible material fields
		if( dbQuery('field')->Active(1)->exec($this->material_fields)) foreach ($this->material_fields as $db_field)
		{
			// Add additional field localization condition
			if ($db_field->local==1) $equal = '(('.$t_name.'.FieldID = '.$db_field->id.')&&('.$t_name.".locale = '".locale()."'))";	
			else $equal = '(('.$t_name.'.FieldID = '.$db_field->id.')&&('.$t_name.".locale = ''))";

			// Define field value column
			$v_col = 'Value'; 	
			if( $db_field->Type == 7 ) $v_col = 'numeric_value';
			$select_data[] = "\n".' MAX(IF('.$equal.','.$t_name.'.`'.$v_col.'`, NULL)) as `'.$db_field->Name.'`';			
			
			// Set additional object metadata
			CMSMaterial::$_attributes[ $db_field->Name ] = $db_field->Name;
			CMSMaterial::$_map[ $db_field->Name ] = 'material.'.$db_field->Name;
		}		
		
		// Set additional object metadata
		CMSMaterial::$_sql_select['this'] = ' STRAIGHT_JOIN '.CMSMaterial::$_sql_select['this'];	
		if(sizeof($select_data)) CMSMaterial::$_sql_select['this'] .= ','.implode(',', $select_data);
		CMSMaterial::$_sql_from['this'] .= "\n".'LEFT JOIN materialfield as '.$t_name.' on material.MaterialID = '.$t_name.'.MaterialID';
		CMSMaterial::$_own_group[] = 'material.MaterialID';
	}
	
	/** @see \samson\core\ExternalModule::init() */
	public function init( array $params = array() )
	{
		// Build navigation tree
		$this->buildNavigation();	

		// Change static class data
		$this->afterCompress();		
		
		// Create cache collection
		dbRecord::$instances[ "samson\cms\cmsmaterial" ] = array();
	}
	
	/** Constructor */
	public function __construct( $path = null )
	{	
		// Установим обработчик e404
		s()->e404( array( $this, 'e404' ) );		
		
		parent::__construct( $path );
	}
}