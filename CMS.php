<?php 
namespace samson\cms;

use samson\activerecord\TableRelation;

use samson\activerecord\CacheTable;
use samson\activerecord\material;
use samson\core\CompressableService;
use samson\activerecord\dbRecord;
use samson\activerecord\dbMySQLConnector;

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
		$sql_user = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."user` (
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
		$sql_gallery = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."gallery` (
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
		$sql_group = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."group` (
		  `GroupID` int(20) NOT NULL AUTO_INCREMENT,
		  `Name` varchar(255) NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`GroupID`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
	
		// SQL команда на добавление таблицы связей пользователей и групп
		$sql_groupright ="CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."groupright` (
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
		$sql_right = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."right` (
		  `RightID` int(20) NOT NULL AUTO_INCREMENT,
		  `Name` varchar(255) NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`RightID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
	
		// Related materials
		$sql_relation_material = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."related_materials` (
		  `related_materials_id` int(11) NOT NULL AUTO_INCREMENT,
		  `first_material` int(11) NOT NULL,
		  `first_locale` varchar(10) NOT NULL,
		  `second_material` int(11) NOT NULL,
		  `second_locale` varchar(10) NOT NULL,
		  PRIMARY KEY (`related_materials_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";		
	
		// SQL команда на добавление таблицы материалов
		$sql_material = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."material` (
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
		$sql_structure = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."structure` (
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
		$sql_structurematerial = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."structurematerial` (
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
		$sql_field = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."field` (
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
		$sql_navfield = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."structurefield` (
		  `StructureFieldID` int(11) NOT NULL AUTO_INCREMENT,
		  `StructureID` int(11) NOT NULL,
		  `FieldID` int(11) NOT NULL,
		  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`StructureFieldID`),
		  KEY `StructureID` (`StructureID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

		// SQL комманда на создание таблицы связей материалов и полей
		$sql_materialfield = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."materialfield` (
		  `MaterialFieldID` int(11) NOT NULL AUTO_INCREMENT,
		  `FieldID` int(11) NOT NULL,
		  `MaterialID` int(11) NOT NULL,
		  `Value` text NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`MaterialFieldID`),
		  KEY `MaterialID` (`MaterialID`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        $sql_structure_relation = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."structure_relation` (
		  `structure_relation_id` int(11) NOT NULL AUTO_INCREMENT,
		  `parent_id` int(11) NOT NULL,
		  `child_id` int(11) NOT NULL,
		  PRIMARY KEY (`structure_relation_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
		
		// SQL table for storing database version
		$sql_version = " CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."cms_version` ( `version` varchar(15) not null default '1')
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
        db()->simple_query( $sql_structure_relation);
		db()->simple_query("INSERT INTO `".dbMySQLConnector::$prefix."user` (`UserID`, `FName`, `SName`, `TName`, `Email`, `Password`, `md5_Email`, `md5_Password`, `Created`, `Modyfied`, `GroupID`, `Active`, `Online`, `LastLogin`) VALUES
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
        new TableRelation( 'structurematerial', 'material', 'MaterialID', TableRelation::T_ONE_TO_MANY  );
		new TableRelation( 'structure', 'material', 'MaterialID' );
		new TableRelation( 'structure', 'user', 'UserID' );
		new TableRelation( 'related_materials', 'material', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID' );
		new TableRelation( 'related_materials', 'materialfield', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID' );
		new TableRelation( 'field', 'structurefield', 'FieldID' );
		new TableRelation( 'field', 'structure', 'structurefield.StructureID' );		
		new TableRelation( 'structurefield', 'field', 'FieldID'  );
		new TableRelation( 'structurefield', 'materialfield', 'FieldID'  );		
		new TableRelation( 'structurefield', 'material', 'materialfield.MaterialID'  );
        new TableRelation( 'structure', 'structure_relation', 'StructureID', TableRelation::T_ONE_TO_MANY, 'parent_id', 'children_relations' );
        new TableRelation( 'structure', 'structure', 'children_relations.child_id', TableRelation::T_ONE_TO_MANY, 'StructureID', 'children' );
        new TableRelation( 'structure', 'structure_relation', 'StructureID', TableRelation::T_ONE_TO_MANY, 'child_id', 'parents_relations' );
        new TableRelation( 'structure', 'structure', 'parents_relations.parent_id', TableRelation::T_ONE_TO_MANY, 'StructureID', 'parents' );
        new TableRelation( 'structurematerial', 'structure_relation', 'StructureID', TableRelation::T_ONE_TO_MANY, 'parent_id' );
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
			db()->simple_query("ALTER TABLE  `".dbMySQLConnector::$prefix."cms_version` CHANGE  `version`  `version` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '".$to_version."';");
		}
		// Return current database version
		else 
		{
			$version_row = db()->query('SHOW COLUMNS FROM `'.dbMySQLConnector::$prefix.'cms_version`');
			return $version_row[0]['Default'];
		}
	}
	
	/** Automatic migration to new CMS table structure */
	public function migrate_1_to_2()
	{		
		elapsed('Removing `Relations` table');
		db()->simple_query('DROP TABLE '.dbMySQLConnector::$prefix.'relations');
		
		elapsed('Adding `numeric_value` field into `materialfield` table');
		db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'materialfield` ADD  `numeric_value` INT( 255 ) NOT NULL AFTER  `Value`');
		
		elapsed('Adding `locale` field into `material` table');
		db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material` ADD  `locale` varchar( 2 ) NOT NULL AFTER `Name`');	
		
		elapsed('Removing `Draftmaterial` field from `material` table');
		db()->simple_query('ALTER TABLE `'.dbMySQLConnector::$prefix.'material` DROP `Draftmaterial`');
		
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
		
		elapsed('Changing `'.dbMySQLConnector::$prefix.'material` table columns order');
		db()->simple_query('ALTER TABLE `'.dbMySQLConnector::$prefix.'material` MODIFY `Teaser` TEXT AFTER `Content`');
		db()->simple_query('ALTER TABLE `'.dbMySQLConnector::$prefix.'material` MODIFY `Published` INT(1) UNSIGNED AFTER `Draft`');
		db()->simple_query('ALTER TABLE `'.dbMySQLConnector::$prefix.'material` MODIFY `Active` INT(1) UNSIGNED AFTER `Published`');
		db()->simple_query('ALTER TABLE `'.dbMySQLConnector::$prefix.'material` MODIFY `UserID` INT(11) AFTER `Title`');
		db()->simple_query('ALTER TABLE `'.dbMySQLConnector::$prefix.'material` MODIFY `Modyfied` TIMESTAMP AFTER `Title`');		
		db()->simple_query('ALTER TABLE `'.dbMySQLConnector::$prefix.'material` MODIFY `Created` DATETIME AFTER `Title`');
	}
	
	/** Automatic migration to new CMS table structure */
	public function migrate_2_to_3()
	{	
		elapsed('Adding `locale` field into `structure` table');
		db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structure` ADD  `locale` VARCHAR( 10 ) NOT NULL AFTER  `Name` ;');
	}
	
	/** Automatic migration to new CMS table structure */
	public function migrate_3_to_4()
	{
		elapsed('Adding `locale` field into `materialfield` table');
		db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'materialfield` ADD  `locale` VARCHAR( 10 ) NOT NULL AFTER  `numeric_value` ;');
		elapsed('Adding `local` field into `field` table');
		db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'field` ADD  `local` int( 10 ) NOT NULL AFTER  `Type` ;');
	}

    public function migrate_4_to_5()
    {
        if(!dbQuery('field')->Name('Content')->first($db_field))
        {
            $db_field = new \samson\activerecord\field(false);
            $db_field->Name = 'Content';
            $db_field->Type = 8;
            $db_field->Active = 1;
            $db_field->save();
        }

        // Create structure for all materials
        if(!dbQuery('structure')->Url('__material')->Active(1)->first($db_structure))
        {
            $db_structure = new \samson\activerecord\structure(false);
            $db_structure->Name = 'Материал';
            $db_structure->Url = '__material';
            $db_structure->Active = 1;
            if(dbQuery('user')->first($db_user)) $db_structure->UserID = $db_user->id;
            $db_structure->save();
        }

        if(!dbQuery('structurefield')->FieldID($db_field->id)->StructureID($db_structure->id)->Active(1)->first($db_sf))
        {
            $db_structurefield = new \samson\activerecord\structurefield(false);
            $db_structurefield->FieldID = $db_field->id;
            $db_structurefield->StructureID = $db_structure->id;
            $db_structurefield->Active = 1;
            $db_structurefield->save();
        }

        if(dbQuery('material')->Active(1)->Draft(0)->exec($db_materials))
        {
            foreach($db_materials as $db_material)
            {
                //if(isset($db_material->Content{0}))
                {
                    if(!dbQuery('materialfield')->MaterialID($db_material->id)->FieldID($db_field->id)->Active(1)->first($db_mf))
                    {
                        $db_mf = new \samson\activerecord\materialfield(false);
                        $db_mf->MaterialID = $db_material->id;
                        $db_mf->FieldID = $db_field->id;
                        $db_mf->Active = 1;
                        $db_mf->Value = $db_material->Content;
                        $db_mf->save();

                        $db_sm =new \samson\activerecord\structurematerial(false);
                        $db_sm->StructureID = $db_structure->id;
                        $db_sm->MaterialID = $db_material->id;
                        $db_sm->Active = 1;
                        $db_sm->save();
                    }
                }
            }
        }

        db()->simple_query('ALTER TABLE  `material` DROP  `Content`');
    }

    public function migrate_5_to_6()
    {
        // Convert all old "date" fields to numeric for fixing db requests
        if (dbQuery('field')->Type(3)->fields('id',$fields)) {
            foreach( dbQuery('materialfield')->FieldID($fields)->exec() as $mf) {
                $mf->numeric_value = strtotime($mf->Value);
                $mf->save();
            }
        }

    }

    public function migrate_6_to_7()
    {
        $db_structures = null;
        // Convert all old "date" fields to numeric for fixing db requests
        if (dbQuery('structure')->Active(1)->exec($db_structures)) {
            foreach( $db_structures as $db_structure) {
                $relation = new \samson\activerecord\structure_relation(false);
                $relation->parent_id = $db_structure->ParentID;
                $relation->child_id = $db_structure->id;
                $relation->save();
            }
        }
    }

    public function migrate_7_to_8()
    {

    }

    public function migrate_8_to_9()
    {
        ini_set('memory_limit','4000M');
        set_time_limit(2000);
        $dbFields = array();
        if (dbQuery('field')->local(1)->fields('FieldID', $dbFields)) {
            $dbMFs = array();
            if (dbQuery('materialfield')->FieldID($dbFields)->locale('')->exec($dbMFs)){
                foreach($dbMFs as $dbMF) {
                    $dbMF->locale = 'ru';
                    $dbMF->save();
                }
            }
        }
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
		if( $selector instanceof $classname ) return $selector;
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
		else if( dbQuery($classname)
            ->cond('Active',1)
            ->cond( $field, $selector )
            ->join('children_relations')
            ->join('children', '\samson\cms\cmsnav')
            ->join('parents_relations')
            ->join('parents', '\samson\cms\cmsnav')
            ->first( $cmsnav )) {
            $cmsnav->prepare();
        }
		
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

			// Define field value DB column for storing data
			$v_col = 'Value';
            // We must get data from other column for this type of field
			if ( $db_field->Type == 7 ) {
                $v_col = 'numeric_value';
            }
            else if( $db_field->Type == 3 ) {
                $v_col = 'numeric_value';
            }
			$select_data[] = "\n".' MAX(IF('.$equal.','.$t_name.'.`'.$v_col.'`, NULL)) as `'.$db_field->Name.'`';			
			
			// Set additional object metadata
			CMSMaterial::$_attributes[ $db_field->Name ] = $db_field->Name;
			CMSMaterial::$_map[ $db_field->Name ] = dbMySQLConnector::$prefix.'material.'.$db_field->Name;
		}		
		
		// Set additional object metadata
		CMSMaterial::$_sql_select['this'] = ' STRAIGHT_JOIN '.CMSMaterial::$_sql_select['this'];	
		if(sizeof($select_data)) CMSMaterial::$_sql_select['this'] .= ','.implode(',', $select_data);
		CMSMaterial::$_sql_from['this'] .= "\n".'LEFT JOIN '.dbMySQLConnector::$prefix.'materialfield as '.$t_name.' on '.dbMySQLConnector::$prefix.'material.MaterialID = '.$t_name.'.MaterialID';
		CMSMaterial::$_own_group[] = dbMySQLConnector::$prefix.'material.MaterialID';
	}
	
	/** @see \samson\core\ExternalModule::init() */
	public function init( array $params = array() )
	{
		// Build navigation tree
		//$this->buildNavigation();

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