<?php
namespace samson\cms;

use samson\activerecord\dbRelation;
use samson\activerecord\TableRelation;
use samson\activerecord\material;
use samson\core\CompressableService;
use samson\activerecord\dbRecord;
use samson\activerecord\dbMySQLConnector;

class CMS extends CompressableService
{
    /**
     * Get materials count grouped by structure selectors
     * @param mixed  $selectors Collection of structures selectors to group materials
     * @param string $selector  Selector to find structures, [Url] is used by default
     * @param callable $handler  External handler
     *
     * @return \integer[] Collection where key is structure selector and value is materials count
     */
    public static function getMaterialsCountByStructures($selectors, $selector = 'Url', $handler = null)
    {
        // If not array is passed
        if(!is_array($selectors)) {
            // convert it to array
            $selectors = array($selectors);
        }

        /** @var integer[] $results Collection of materials count grouped by selectors as array keys */
        $results = array_flip($selectors);

        /** @var Navigation[] $countData */
        $countData = null;

        $query = dbQuery('structure')
            ->cond($selector, $selectors)
            ->add_field('Count(material.materialid)', '__Count', false)
            ->join('structurematerial')
            ->join('material')
            ->cond('material_Active', 1)
            ->cond('material_Published', 1)
            ->cond('material_Draft', 0)
            ->group_by('structureid');

        if (is_callable($handler)) {
            call_user_func($handler, array(& $query));
        }
        // Perform db request to get materials count by passed structure selectors
        if ($query->exec($countData)) {
            foreach ($countData as $result) {
                // Check if we have this structure in results array
                if (isset($results[$result->Url])) {
                    // Store materials count
                    $results[$result->Url.'Count'] = $result->__Count;
                }
            }
        }
        foreach ($selectors as $select) {
            unset($results[$select]);
        }

        return $results;
    }

    /**
     * Generic optimized method to find materials by structures
     * with ability to add custom external db request handler.
     *
     * Method makes two requests and performs them as quick as possible
     *
     * @param mixed     $structures     Identifier of structure, or collection of them
     * @param array     $materials      Collection  where results will be returned
     * @param string    $className      Class name of final result objects, must be Material ancestor
     * @param callable  $handler        External function to change generic query(add conditions and etc.)
     * @param array     $handlerParams  External handler additional parameters collection to pass to handler
     *
     * @return bool True if materials ancestors has been found
     */
    public static function getMaterialsByStructures($structures, & $materials = array(), $className = 'samson\cms\CMSMaterial', $handler = null, array $handlerParams = array())
    {
        // If not array of structures is passed - create it
        $structures = is_array($structures) ? $structures : array($structures);

        // Create query to get materials for current structure
        $query = dbQuery('samson\cms\CMSNavMaterial')
            ->cond('StructureID', $structures)
            ->join('material')
            ->cond('material_Active', 1)
            ->group_by('MaterialID');

        // If external request handler is passed - use it
        if (is_callable($handler)) {
            // Call external query handler
            if (call_user_func_array($handler, array_merge(array(&$query), $handlerParams)) === false) {
                // Someone else has failed my lord
                return false;
            }
        }

        // Perform request to find all matched material ids
        $ids = array();
        if ($query->fieldsNew('MaterialID', $ids)) {
            // Perform CMSMaterial request with handlers
            if (dbQuery($className)->cond('MaterialID', $ids)->join('samson\cms\cmsgallery')->exec($materials)) {
                return true;
            }
        }

        //I have failed my lord
        return false;
    }

    /** Identifier */
    protected $id = 'cmsapi';

    /**
     * Collection of material additional fields
     * @deprecated TODO: Remove!
     */
    public $material_fields = array();

    /** @var string[] Collection of material fields SQL commands to include into SQL SELECT statement */
    public static $fields = array();

    /** @var array Collection of original material table attributes before spoofing */
    public static $materialAttributes = array();

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
		  `md5_email` varchar(255) NOT NULL,
		  `md5_password` varchar(255) NOT NULL,
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
		  `structure_id` int(11) NOT NULL,
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

        // SQL комманда на создание таблицы связей между структурами
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
        new TableRelation( 'structure', 'material', 'structurematerial.MaterialID', TableRelation::T_ONE_TO_MANY, null, 'manymaterials');
        new TableRelation( 'structure', 'gallery', 'structurematerial.MaterialID', TableRelation::T_ONE_TO_MANY, null, 'manymaterials');
        /*new TableRelation( 'structure', 'material', 'MaterialID' );*/
        new TableRelation( 'structure', 'user', 'UserID' );
        new TableRelation( 'structure', 'materialfield', 'material.MaterialID', TableRelation::T_ONE_TO_MANY, 'MaterialID', '_mf');
        new TableRelation( 'structure', 'structurematerial', 'StructureID', TableRelation::T_ONE_TO_MANY );
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
    public function migrator($to_version = null)
    {
        // If something passed - change database version to it
        if( func_num_args() ) {
            // Save current version to special db table
            db()->simple_query("ALTER TABLE  `".dbMySQLConnector::$prefix."cms_version` CHANGE  `version`  `version` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '".$to_version."';");
            die('Database successfully migrated to ['.$to_version.']');
        }
        // Return current database version
        else {
            $version_row = db()->query('SHOW COLUMNS FROM `'.dbMySQLConnector::$prefix.'cms_version`');
            return $version_row[0]['Default'];
        }
    }

    /** Automatic migration to new CMS table structure */
    public function migrate_1_to_2()
    {
        elapsed('Removing `Relations` table');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'relations');

        elapsed('Removing old localized tables if they exists table');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'enstructure');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'enstructurematerial');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'enstructurefield');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'enfield');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'enmaterial');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'enmaterialfield');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'uastructure');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'uastructurematerial');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'uastructurefield');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'uafield');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'uamaterial');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'uamaterialfield');

        elapsed('Removing old group/right tables if they exists table');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'`group`');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'`right`');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'groupright');
        db()->simple_query('DROP TABLE IF EXISTS '.dbMySQLConnector::$prefix.'mem_cache');

        elapsed('Adding `numeric_value` field into `materialfield` table');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'materialfield` ADD  `numeric_value` INT( 255 ) NOT NULL AFTER  `Value`');

        elapsed('Adding `locale` field into `material` table');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material` ADD  `locale` varchar( 2 ) NOT NULL AFTER `Name`');

        elapsed('Removing `Draftmaterial` field from `material` table');
        db()->simple_query('ALTER TABLE `'.dbMySQLConnector::$prefix.'material` DROP `Draftmaterial`');

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

    /**
     * This migration creates new structure called "Материал" and two new additional fields Content and Teaser
     * and moves all materials columns values to this new additional fields and then removes columns from
     * material table
     */
    public function migrate_4_to_5()
    {
        $this->materialColumnToField('Content', 'material');
    }

    public function migrate_5_to_6()
    {
        $this->materialColumnToField('Teaser', 'material');

        // Convert all old "date" fields to numeric for fixing db requests
        if (dbQuery('field')->Type(3)->fields('id',$fields)) {
            foreach (dbQuery('materialfield')->FieldID($fields)->exec() as $mf) {
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
            foreach ($db_structures as $db_structure) {
                $relation = new \samson\activerecord\structure_relation(false);
                $relation->parent_id = $db_structure->ParentID;
                $relation->child_id = $db_structure->id;
                $relation->save();
            }
        }
    }

    public function migrate_7_to_8()
    {
        elapsed('Adding `StructureID` field into `material` table');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material` ADD  `structure_id` INT( 255 ) NOT NULL AFTER  `Active`');
    }

    public function migrate_8_to_9()
    {
        elapsed('Adding `filter` table');
        elapsed('Adding `filtered` field into `field` table');
        // SQL комманда на создание таблицы фильтров
        $sql_filter = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix."filter` (
		  `filter_id` int(11) NOT NULL AUTO_INCREMENT,
		  `field_id` int(11) NOT NULL,
		  `value` varchar(255) NOT NULL,
		  `locale` VARCHAR( 10 ) NOT NULL,
		  PRIMARY KEY (`filter_id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
        db()->simple_query($sql_filter);

        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'field` ADD  `filtered` INT( 10 ) NOT NULL AFTER  `local`');
    }
    /* added index key**/
    public function migrate_9_to_10()
    {
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'user`                ADD INDEX (`GroupID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'gallery`             ADD INDEX (`MaterialID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'groupright`          ADD INDEX (`GroupID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'groupright`          ADD INDEX (`RightID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material`            ADD INDEX (`structure_id`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material`            ADD INDEX (`UserID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structure`           ADD INDEX (`ParentID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structure`           ADD INDEX (`UserID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structure`           ADD INDEX (`MaterialID`)');
        //db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'field`               ADD INDEX (`UserID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'field`               ADD INDEX (`ParentID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structurefield`      ADD INDEX (`FieldID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'materialfield`       ADD INDEX (`FieldID`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structure_relation`  ADD INDEX (`parent_id`)');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structure_relation`  ADD INDEX (`child_id`)');
    }

    // Add system fields
    public function migrate_10_to_11()
    {
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material` ADD `system` INT(1) NOT NULL DEFAULT 0');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structure` ADD `system` INT(1) NOT NULL DEFAULT 0');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'field` ADD `system` INT(1) NOT NULL DEFAULT 0');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'user` ADD `system` INT(1) NOT NULL DEFAULT 0');

        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material` DROP `locale`');
    }

    /**
     * This migration creates new structure called "SEO" and three new additional fields Description, Title, Keywords
     * and moves all materials columns values to this new additional fields and then removes columns from
     * material table
     */
    public function migrate_11_to_12()
    {
        $this->materialColumnToField('Description', 'seo');

        $structure = null;
        if(dbQuery('structure')->Name('material')->first($structure)) {
            $structure->system = 1;
            $structure->save();
        }

        $structure = null;
        if(dbQuery('structure')->Name('seo')->first($structure)) {
            $structure->system = 1;
            $structure->save();
        }

        $field = null;
        if(dbQuery('field')->Name('Content')->first($field)) {
            $field->system = 1;
            $field->save();
        }

        $field = null;
        if(dbQuery('field')->Name('Teaser')->first($field)) {
            $field->system = 1;
            $field->save();
        }
    }

    /**
     * This migration creates new structure called "SEO" and three new additional fields Description, Title, Keywords
     * and moves all materials columns values to this new additional fields and then removes columns from
     * material table
     */
    public function migrate_12_to_13()
    {
        $this->materialColumnToField('Keywords', 'seo');
    }

    /**
     * This migration creates new structure called "SEO" and three new additional fields Description, Title, Keywords
     * and moves all materials columns values to this new additional fields and then removes columns from
     * material table
     */
    public function migrate_13_to_14()
    {
        $this->materialColumnToField('Title', 'seo');
    }

    /**
     * Add related materials fields
     */
    public function migrate_14_to_15()
    {
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material` ADD `parent_id` INT(11) NOT NULL DEFAULT 0 AFTER `MaterialID`');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material` ADD `type` INT(1) NOT NULL DEFAULT 0 AFTER `Draft`');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structure` ADD `type` INT(1) NOT NULL DEFAULT 0 AFTER `PriorityNumber`');
        db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'field` ADD `UserID` INT(11) NOT NULL DEFAULT 0 AFTER `PriorityNumber`');
    }

    public function materialColumnToField($column, $structure)
    {
        // Find first user
        $user = null;
        if(dbQuery('user')->first($user)) {

        }

        // Create structure for all materials
        $db_structure = null;
        if (!dbQuery('structure')->Url('__'.$structure)->Active(1)->first($db_structure)) {
            $db_structure = new \samson\activerecord\structure(false);
            $db_structure->Name = $structure;
            $db_structure->Url = '__'.$structure;
            $db_structure->Active = 1;
            $db_structure->UserID = $user->id;
            $db_structure->system = 1;
            $db_structure->save();
        }

        $dbField = null;
        if (!dbQuery('field')->Name($column)->first($dbField)) {
            $dbField = new \samson\activerecord\field(false);
            $dbField->Name = $column;
            $dbField->Type = 8;
            $dbField->Active = 1;
            $dbField->system = 1;
            $dbField->save();
        }

        // Create structure field relations
        $db_sf = null;
        if (!dbQuery('structurefield')->FieldID($dbField->id)->StructureID($db_structure->id)->Active(1)->first($db_sf)) {
            $db_sf = new \samson\activerecord\structurefield(false);
            $db_sf->FieldID = $dbField->id;
            $db_sf->StructureID = $db_structure->id;
            $db_sf->Active = 1;
            $db_sf->save();
        }

        // Iterate all existing materials
        $db_materials = array();
        if (dbQuery('material')->Active('1')->Draft('0')->exec($db_materials)) {
            trace('Found materials:'.sizeof($db_materials));
            foreach ($db_materials as $db_material) {
                //trace('Updating material:'.$db_material->id);
                // If current material has no connection with new structure
                $db_sm = null;
                if (!dbQuery('structurematerial')->StructureID($db_structure->id)->MaterialID($db_material->id)->first($db_sm)) {
                    // Create this connection
                    $db_sm = new \samson\activerecord\structurematerial(false);
                    $db_sm->StructureID = $db_structure->id;
                    $db_sm->MaterialID = $db_material->id;
                    $db_sm->Active = 1;
                    $db_sm->save();

                    //trace('Updating structurematerial:'.$db_material->id);
                }

                // If this material has no Content field right now
                $db_mf = null;
                if (!dbQuery('materialfield')->MaterialID($db_material->id)->FieldID($dbField->id)->Active(1)->first($db_mf)) {
                    // Create Content additional field
                    $db_mf = new \samson\activerecord\materialfield(false);
                    $db_mf->MaterialID = $db_material->id;
                    $db_mf->FieldID = $dbField->id;
                    $db_mf->Active = 1;
                    $db_mf->Value = $db_material[$column];
                    $db_mf->save();

                    //trace('Updating materialfield:'.$db_material->id);
                }
            }
        }

        db()->simple_query('ALTER TABLE  `material` DROP  `'.$column.'`');
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
    public function & material($selector, $field = 'Url')
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
    public function & navigation($selector, $field = 'Url')
    {
        $cmsnav = null;

        // If no selector passed
        if( !isset($selector) ) return $cmsnav;

        // If id passed switch to real table column name
        if( $field == 'id' ) $field = 'StructureID';

        // Build classname with PHP < 5.3 compatibility
        $classname = \samson\core\AutoLoader::className('CMSNav', 'samson\cms');

        // If instance of CMSNav passed - just return it
        if( is_a( $selector, $classname)) return $selector;
        // Try to search activerecord instances cache by selector
        else if( isset(dbRecord::$instances[$classname][$selector]) ) {$cmsnav = & dbRecord::$instances[$classname][$selector];}
        // Perform request to database
        else if( dbQuery($classname)
            ->cond('Active',1)
            ->cond( $field, $selector )
            ->join('children_relations')
            ->join('children', '\samson\cms\CMSNav')
            ->join('parents_relations')
            ->join('parents', '\samson\cms\CMSNav')
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
     * @return array
     */
    public function & navmaterials($selector, $field = 'Url', $handler = null)
    {
        $result = array();

        // Find CMSNav
        if(null !== ($db_nav = $this->navigation($selector, $field)))
        {
            // Get material ids from structure materials records
            $ids = array();
            if(dbQuery('samson\cms\CMSNavMaterial')->cond( 'StructureID', $db_nav->id )->fields( 'MaterialID', $ids ))
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
    }

    public function buildNavigation()
    {
        dbRecord::$instances['samson\cms\CMSNav'] = array();

        CMSNav::$top = new CMSNav( false );
        CMSNav::$top->Name = 'Корень навигации';
        CMSNav::$top->Url = 'NAVIGATION_BASE';
        CMSNav::$top->StructureID = 0;
        // Try load navigation cache from memmory cache
        //if( ! CacheTable::ifget('cms_navigation_cache', dbRecord::$instances['samson\cms\CMSNav'] ) )
        {
            // Perform request to db
            $cmsnavs = dbQuery('samson\cms\CMSNav')->cond('Active',1)
                //->cond('locale', locale())
                ->order_by('PriorityNumber','asc')->exec();

            foreach ( $cmsnavs as $cmsnav )
            {
                dbRecord::$instances['samson\cms\CMSNav'][ $cmsnav->Url ] = $cmsnav;
            }

            // Save all array to memmory cache
            //CacheTable::set( 'cms_navigation_cache', dbRecord::$instances['samson\cms\CMSNav'] );
        }

        //trace(dbRecord::$instances['samson\cms\CMSNav']);

        // Build navigation tree
        CMSNav::build( CMSNav::$top, dbRecord::$instances['samson\cms\CMSNav'] );

        //trace($cmsnavs);
    }

    /** @see \samson\core\CompressableExternalModule::afterCompress() */
    public function afterCompress( & $obj = null, array & $code = null )
    {
        // Fill additional fields data to material db request data for automatic altering material request
        self::$fields = array();

        $t_name = '_mf';

        // Save original material attributes
        self::$materialAttributes = CMSMaterial::$_attributes;

        // Perform db query to get all possible material fields
        if( dbQuery('field')->Active(1)->Name('', dbRelation::NOT_EQUAL)->exec($this->material_fields)) foreach ($this->material_fields as $db_field)
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

            // Save additional field
            self::$fields[$db_field->Name] = "\n".' MAX(IF('.$equal.','.$t_name.'.`'.$v_col.'`, NULL)) as `'.$db_field->Name.'`';

            // Set additional object metadata
            CMSMaterial::$_attributes[ $db_field->Name ] = $db_field->Name;
            CMSMaterial::$_map[ $db_field->Name ] = dbMySQLConnector::$prefix.'material.'.$db_field->Name;
        }

        // Set additional object metadata
        CMSMaterial::$_sql_select['this'] = ' STRAIGHT_JOIN '.CMSMaterial::$_sql_select['this'];
        if (sizeof(self::$fields)) {
            CMSMaterial::$_sql_select['this'] .= ','.implode(',', self::$fields);
        }
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
        dbRecord::$instances[ "samson\cms\CMSMaterial" ] = array();
    }

    /** Constructor */
    public function __construct( $path = null )
    {
        parent::__construct( $path );
    }
}
