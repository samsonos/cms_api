<?php
namespace samson\cms;

use samson\activerecord\dbRelation;
use samson\activerecord\field;
use samson\activerecord\materialfield;
use samson\activerecord\structure;
use samson\activerecord\structurefield;
use samson\activerecord\structurematerial;
use samson\activerecord\TableRelation;
use samson\activerecord\material;
use samson\core\CompressableService;
use samson\activerecord\dbRecord;
use samson\activerecord\dbMySQLConnector;

class CMS extends CompressableService
{
    /**
     * Get materials count grouped by structure selectors
     * @param mixed $selectors Collection of structures selectors to group materials
     * @param string $selector Selector to find structures, [Url] is used by default
     * @param callable $handler External handler
     *
     * @return \integer[] Collection where key is structure selector and value is materials count
     */
    public static function getMaterialsCountByStructures($selectors, $selector = 'Url', $handler = null)
    {
        // If not array is passed
        if (!is_array($selectors)) {
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
                    $results[$result->Url . 'Count'] = $result->__Count;
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
     * @param mixed $structures Identifier of structure, or collection of them
     * @param array $materials Collection  where results will be returned
     * @param string $className Class name of final result objects, must be Material ancestor
     * @param callable $handler External function to change generic query(add conditions and etc.)
     * @param array $handlerParams External handler additional parameters collection to pass to handler
     *
     * @return bool True if materials ancestors has been found
     */
    public static function getMaterialsByStructures($structures, & $materials = array(), $className = 'samson\cms\CMSMaterial', $handlers = null, array $handlerParams = array(), $innerHandler = null)
    {
        // If not array of structures is passed - create it
        $structures = is_array($structures) ? $structures : array($structures);

        // Create query to get materials for current structure
        $query = dbQuery('samson\cms\CMSNavMaterial')
            ->cond('StructureID', $structures)
            ->join('material')
            ->cond('material_Active', 1)
            ->group_by('MaterialID');

        // Convert external handler to array of handlers for backward compatibility
        $handlers = is_callable($handlers) ? array($handlers) : $handlers;

        // Iterate all handlers
        for ($i = 0, $size = sizeof($handlers); $i < $size; $i++) {

            // Generic handler parameters array definition if we have parameters for i handle
            $hParams = isset($handlerParams[$i]) ? $handlerParams[$i] : array();
            // If this is an array of parameters
            if (!is_array($hParams)) {
                $hParams = array($handlerParams[$i]);
            }

            // Create parameters collection
            $params = array_merge(
                array($handlers[$i]), // First element is callable array or string
                $hParams // Possible additional callable parameters
            );

            // Call external query handler
            if (call_user_func_array(array($query, 'handler'), $params) === false) {
                // Someone else has failed my lord
                return false;
            }
        }

        // Perform request to find all matched material ids
        $ids = array();
        if ($query->fields('MaterialID', $ids)) {
            // Create inner query
            $innerQuery = dbQuery($className)->cond('MaterialID', $ids);

            // Set inner query handler if passed
            if (is_callable($innerHandler)) {
                // Call external query handler
                if (call_user_func_array($innerHandler, array_merge(array(&$innerQuery))) === false) {
                    // Someone else has failed my lord
                    return false;
                }
            }

            // Perform CMSMaterial request with handlers
            if ($innerQuery->exec($materials)) {
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
        $sql_user = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "user` (
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
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL команда на добавление таблицы пользователей
        $sql_gallery = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "gallery` (
		  `PhotoID` int(11) NOT NULL AUTO_INCREMENT,
		  `MaterialID` int(11) NOT NULL,
		  `Path` varchar(255) NOT NULL,
		  `Src` varchar(255) NOT NULL,
		  `Loaded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Description` text NOT NULL,
		  `Name` varchar(255) NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`PhotoID`)
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL команда на добавление таблицы групп пользователей
        $sql_group = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "group` (
		  `GroupID` int(20) NOT NULL AUTO_INCREMENT,
		  `Name` varchar(255) NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`GroupID`)
		) ENGINE=INNODB DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL команда на добавление таблицы связей пользователей и групп
        $sql_groupright = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "groupright` (
		  `GroupRightID` int(11) NOT NULL AUTO_INCREMENT,
		  `GroupID` int(10) NOT NULL,
		  `RightID` int(20) NOT NULL,
		  `Entity` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '_',
		  `Key` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
		  `Ban` int(10) NOT NULL,
		  `TS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`GroupRightID`)
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;";

        // SQL команда на добавление таблицы прав пользователей
        $sql_right = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "right` (
		  `RightID` int(20) NOT NULL AUTO_INCREMENT,
		  `Name` varchar(255) NOT NULL,
		  `Description` varchar(255) NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`RightID`)
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // Related materials
        $sql_relation_material = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "related_materials` (
		  `related_materials_id` int(11) NOT NULL AUTO_INCREMENT,
		  `first_material` int(11) NOT NULL,
		  `first_locale` varchar(10) NOT NULL,
		  `second_material` int(11) NOT NULL,
		  `second_locale` varchar(10) NOT NULL,
		  PRIMARY KEY (`related_materials_id`)
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL команда на добавление таблицы материалов
        $sql_material = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "material` (
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
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL команда на добавление таблицы навигации
        $sql_structure = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "structure` (
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
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL комманда на создание таблицы связей навигации и материалов
        $sql_structurematerial = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "structurematerial` (
		  `StructureMaterialID` int(11) NOT NULL AUTO_INCREMENT,
		  `StructureID` int(11) NOT NULL,
		  `MaterialID` int(11) NOT NULL,
		  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Active` int(11) NOT NULL DEFAULT '1',
		  PRIMARY KEY (`StructureMaterialID`),
		  KEY `StructureID` (`StructureID`),
		  KEY `MaterialID` (`MaterialID`)
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL команда на добавление таблицы полей
        $sql_field = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "field` (
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
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL команда на добавление таблицы связей ЄНС с полями
        $sql_navfield = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "structurefield` (
		  `StructureFieldID` int(11) NOT NULL AUTO_INCREMENT,
		  `StructureID` int(11) NOT NULL,
		  `FieldID` int(11) NOT NULL,
		  `Modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`StructureFieldID`),
		  KEY `StructureID` (`StructureID`)
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL комманда на создание таблицы связей материалов и полей
        $sql_materialfield = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "materialfield` (
		  `MaterialFieldID` int(11) NOT NULL AUTO_INCREMENT,
		  `FieldID` int(11) NOT NULL,
		  `MaterialID` int(11) NOT NULL,
		  `Value` text NOT NULL,
		  `Active` int(11) NOT NULL,
		  PRIMARY KEY (`MaterialFieldID`),
		  KEY `MaterialID` (`MaterialID`)
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL комманда на создание таблицы связей между структурами
        $sql_structure_relation = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "structure_relation` (
		  `structure_relation_id` int(11) NOT NULL AUTO_INCREMENT,
		  `parent_id` int(11) NOT NULL,
		  `child_id` int(11) NOT NULL,
		  PRIMARY KEY (`structure_relation_id`)
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // SQL table for storing database version
        $sql_version = " CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "cms_version` ( `version` varchar(15) not null default '1')
				ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";

        // Выполним SQL комманды
        db()->query($sql_version);
        db()->query($sql_field);
        db()->query($sql_navfield);
        db()->query($sql_materialfield);
        db()->query($sql_material);
        db()->query($sql_structure);
        db()->query($sql_structurematerial);
        db()->query($sql_user);
        db()->query($sql_group);
        db()->query($sql_right);
        db()->query($sql_groupright);
        db()->query($sql_relation_material);
        db()->query($sql_gallery);
        db()->query($sql_structure_relation);

        // Initiate migration mechanism
        db()->migration(get_class($this), array($this, 'migrator'));

        // Define permanent table relations
        new TableRelation('material', 'user', 'UserID', 0, 'user_id');
        new TableRelation('material', 'gallery', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('material', 'materialfield', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('material', 'field', 'materialfield.FieldID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('material', 'structurematerial', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('material', 'structure', 'structurematerial.StructureID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('materialfield', 'field', 'FieldID');
        new TableRelation('materialfield', 'material', 'MaterialID');
        new TableRelation('structurematerial', 'structure', 'StructureID');
        new TableRelation('structurematerial', 'materialfield', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('structurematerial', 'material', 'MaterialID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('structure', 'material', 'structurematerial.MaterialID', TableRelation::T_ONE_TO_MANY, null, 'manymaterials');
        new TableRelation('structure', 'gallery', 'structurematerial.MaterialID', TableRelation::T_ONE_TO_MANY, null, 'manymaterials');
        /*new TableRelation( 'structure', 'material', 'MaterialID' );*/
        new TableRelation('structure', 'user', 'UserID', 0, 'user_id');
        new TableRelation('structure', 'materialfield', 'material.MaterialID', TableRelation::T_ONE_TO_MANY, 'MaterialID', '_mf');
        new TableRelation('structure', 'structurematerial', 'StructureID', TableRelation::T_ONE_TO_MANY);
        new TableRelation('related_materials', 'material', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID');
        new TableRelation('related_materials', 'materialfield', 'first_material', TableRelation::T_ONE_TO_MANY, 'MaterialID');
        new TableRelation('field', 'structurefield', 'FieldID');
        new TableRelation('field', 'structure', 'structurefield.StructureID');
        new TableRelation('structurefield', 'field', 'FieldID');
        new TableRelation('structurefield', 'materialfield', 'FieldID');
        new TableRelation('structurefield', 'material', 'materialfield.MaterialID');
        new TableRelation('structure', 'structure_relation', 'StructureID', TableRelation::T_ONE_TO_MANY, 'parent_id', 'children_relations');
        new TableRelation('structure', 'structure', 'children_relations.child_id', TableRelation::T_ONE_TO_MANY, 'StructureID', 'children');
        new TableRelation('structure', 'structure_relation', 'StructureID', TableRelation::T_ONE_TO_MANY, 'child_id', 'parents_relations');
        new TableRelation('structure', 'structure', 'parents_relations.parent_id', TableRelation::T_ONE_TO_MANY, 'StructureID', 'parents');
        new TableRelation('structurematerial', 'structure_relation', 'StructureID', TableRelation::T_ONE_TO_MANY, 'parent_id');
        new TableRelation('groupright', 'right', 'RightID', TableRelation::T_ONE_TO_MANY);
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
        if (func_num_args()) {
            // Save current version to special db table
            db()->query("ALTER TABLE  `" . dbMySQLConnector::$prefix . "cms_version` CHANGE  `version`  `version` VARCHAR( 15 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '" . $to_version . "';");
            die('Database successfully migrated to [' . $to_version . ']');
        } else { // Return current database version
            $version_row = db()->fetch('SHOW COLUMNS FROM `' . dbMySQLConnector::$prefix . 'cms_version`');
            return $version_row[0]['Default'];
        }
    }

    /** Automatic migration to new CMS table structure */
    public function migrate_1_to_2()
    {
        elapsed('Removing `Relations` table');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'relations');

        elapsed('Removing old localized tables if they exists table');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'enstructure');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'enstructurematerial');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'enstructurefield');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'enfield');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'enmaterial');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'enmaterialfield');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'uastructure');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'uastructurematerial');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'uastructurefield');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'uafield');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'uamaterial');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'uamaterialfield');

        elapsed('Removing old group/right tables if they exists table');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . '`group`');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . '`right`');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'groupright');
        db()->query('DROP TABLE IF EXISTS ' . dbMySQLConnector::$prefix . 'mem_cache');

        elapsed('Adding `numeric_value` field into `materialfield` table');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'materialfield` ADD  `numeric_value` INT( 255 ) NOT NULL AFTER  `Value`');

        elapsed('Adding `locale` field into `material` table');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material` ADD  `locale` varchar( 2 ) NOT NULL AFTER `Name`');

        elapsed('Removing `Draftmaterial` field from `material` table');
        db()->query('ALTER TABLE `' . dbMySQLConnector::$prefix . 'material` DROP `Draftmaterial`');

        elapsed('Changing `' . dbMySQLConnector::$prefix . 'material` table columns order');
        db()->query('ALTER TABLE `' . dbMySQLConnector::$prefix . 'material` MODIFY `Teaser` TEXT AFTER `Content`');
        db()->query('ALTER TABLE `' . dbMySQLConnector::$prefix . 'material` MODIFY `Published` INT(1) UNSIGNED AFTER `Draft`');
        db()->query('ALTER TABLE `' . dbMySQLConnector::$prefix . 'material` MODIFY `Active` INT(1) UNSIGNED AFTER `Published`');
        db()->query('ALTER TABLE `' . dbMySQLConnector::$prefix . 'material` MODIFY `UserID` INT(11) AFTER `Title`');
        db()->query('ALTER TABLE `' . dbMySQLConnector::$prefix . 'material` MODIFY `Modyfied` TIMESTAMP AFTER `Title`');
        db()->query('ALTER TABLE `' . dbMySQLConnector::$prefix . 'material` MODIFY `Created` DATETIME AFTER `Title`');

        // Check if we did not already create user
        if (!sizeof(db()->fetch('SELECT * from user where email ="admin@admin.com"'))) {
            db()->query("INSERT INTO `" . dbMySQLConnector::$prefix . "user` (`UserID`, `FName`, `SName`, `TName`, `email`, `md5_email`, `md5_password`, `created`, `modyfied`, `active`) VALUES
	 (1, 'Виталий', 'Егоров', 'Игоревич', 'admin@admin.com', '64e1b8d34f425d19e1ee2ea7236d3028', '64e1b8d34f425d19e1ee2ea7236d3028', '2011-10-25 14:59:06', '2013-05-22 11:52:38',  1)
			ON DUPLICATE KEY UPDATE active=1");
        }
    }

    /** Automatic migration to new CMS table structure */
    public function migrate_2_to_3()
    {
        elapsed('Adding `locale` field into `structure` table');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'structure` ADD  `locale` VARCHAR( 10 ) NOT NULL AFTER  `Name` ;');
    }

    /** Automatic migration to new CMS table structure */
    public function migrate_3_to_4()
    {
        elapsed('Adding `locale` field into `materialfield` table');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'materialfield` ADD  `locale` VARCHAR( 10 ) NOT NULL AFTER  `numeric_value` ;');
        elapsed('Adding `local` field into `field` table');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'field` ADD  `local` int( 10 ) NOT NULL AFTER  `Type` ;');
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
        if (dbQuery('field')->Type(3)->fields('id', $fields)) {
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
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material` ADD  `structure_id` INT( 255 ) NOT NULL AFTER  `Active`');
    }

    public function migrate_8_to_9()
    {
        elapsed('Adding `filter` table');
        elapsed('Adding `filtered` field into `field` table');
        // SQL комманда на создание таблицы фильтров
        $sql_filter = "CREATE TABLE IF NOT EXISTS `" . dbMySQLConnector::$prefix . "filter` (
		  `filter_id` int(11) NOT NULL AUTO_INCREMENT,
		  `field_id` int(11) NOT NULL,
		  `value` varchar(255) NOT NULL,
		  `locale` VARCHAR( 10 ) NOT NULL,
		  PRIMARY KEY (`filter_id`)
		) ENGINE=INNODB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
        db()->query($sql_filter);

        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'field` ADD  `filtered` INT( 10 ) NOT NULL AFTER  `local`');
    }

    /* added index key**/
    public function migrate_9_to_10()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'user`                ADD INDEX (`GroupID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'gallery`             ADD INDEX (`MaterialID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'groupright`          ADD INDEX (`GroupID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'groupright`          ADD INDEX (`RightID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material`            ADD INDEX (`structure_id`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material`            ADD INDEX (`UserID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'structure`           ADD INDEX (`ParentID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'structure`           ADD INDEX (`UserID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'structure`           ADD INDEX (`MaterialID`)');
        //db()->query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'field`               ADD INDEX (`UserID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'field`               ADD INDEX (`ParentID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'structurefield`      ADD INDEX (`FieldID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'materialfield`       ADD INDEX (`FieldID`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'structure_relation`  ADD INDEX (`parent_id`)');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'structure_relation`  ADD INDEX (`child_id`)');
    }

    // Add system fields
    public function migrate_10_to_11()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material` ADD `system` INT(1) NOT NULL DEFAULT 0');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'structure` ADD `system` INT(1) NOT NULL DEFAULT 0');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'field` ADD `system` INT(1) NOT NULL DEFAULT 0');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'user` ADD `system` INT(1) NOT NULL DEFAULT 0');

        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material` DROP `locale`');
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
        if (dbQuery('structure')->Name('material')->first($structure)) {
            $structure->system = 1;
            $structure->save();
        }

        $structure = null;
        if (dbQuery('structure')->Name('seo')->first($structure)) {
            $structure->system = 1;
            $structure->save();
        }

        $field = null;
        if (dbQuery('field')->Name('Content')->first($field)) {
            $field->system = 1;
            $field->save();
        }

        $field = null;
        if (dbQuery('field')->Name('Teaser')->first($field)) {
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
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material` ADD `parent_id` INT(11) NOT NULL DEFAULT 0 AFTER `MaterialID`');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material` ADD `type` INT(1) NOT NULL DEFAULT 0 AFTER `Draft`');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'structure` ADD `type` INT(1) NOT NULL DEFAULT 0 AFTER `PriorityNumber`');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'field` ADD `UserID` INT(11) NOT NULL DEFAULT 0 AFTER `PriorityNumber`');
    }

    /**
     * Gallery table changed:
     * Added `size` field
     * Added `priority` field
     * Delete `Thumbpath` field
     * Delete `Thumbsrc` field
     * Path & Src fields automatic correction
     */
    public function migrate_15_to_16()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'gallery` ADD `priority` INT(11) NOT NULL DEFAULT 0 AFTER `MaterialID`');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'gallery` ADD `size` INT(11) NOT NULL DEFAULT 0 AFTER `Src`');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'gallery` DROP `Thumbpath`');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'gallery` DROP `Thumbsrc`');

        foreach (dbQuery('gallery')->exec() as $gallery) {
            $gallery->Path = dirname($gallery->Path);
            $gallery->Src = basename($gallery->Src);
            $gallery->save();
        }
    }

    /**
     * Security improvements - removed password field from user table
     */
    public function migrate_16_to_17()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'user` DROP `Password`');
    }

    /**
     * Fill new fields
     */
    public function migrate_17_to_18()
    {
        /** @var \samson\activerecord\gallery $images */
        $images = null;
        dbQuery('gallery')->cond('Path', '')->exec($images);
        foreach ($images as $image) {
            $oldPath = $image->Src;
            $image->Path = dirname($oldPath) . '/';
            $image->Src = basename($oldPath);
            if (file_exists($oldPath)) {
                $image->Size = filesize($oldPath);
            }
            $image->save();
        }
    }

    /** Added "remains" field to material table */
    public function migrate_18_to_19()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material` ADD `remains` FLOAT NOT NULL DEFAULT 0 AFTER `system`');
    }

    /** Added "access_token" field to user table */
    public function migrate_19_to_20()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'user` ADD `access_token` VARCHAR(256) NOT NULL DEFAULT 0');
    }

    /** Added `priority` field to `field` and `material` tables */
    /** Required for materialtables */
    public function migrate_20_to_21()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'field` ADD `priority` INT(11) NOT NULL DEFAULT 0 AFTER `ParentID`');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'material` ADD `priority` INT(11) NOT NULL DEFAULT 0 AFTER `parent_id`');
    }

    /** Field `numeric_value` in `materialfield` table is now double */
    public function migrate_21_to_22()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'materialfield` MODIFY `numeric_value` DOUBLE NOT NULL DEFAULT 0 AFTER `Value`');
    }

    /** Adding `materialFieldId` to `gallery` table */
    public function migrate_22_to_23()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'gallery` ADD `materialFieldId` INT(11) NOT NULL DEFAULT 0 AFTER `MaterialID`');
    }

    /** Create new gallery from old one */
    public function migrate_23_to_24()
    {
        $user = array_shift(db()->fetch('SELECT * from User Limit 1'));

        // Create field for old gallery
        $field = new field(false);
        $field->Name = '_gallery';
        $field->Type = 9;
        $field->local = 0;
        $field->Description = 'Галерея Материала';
        $field->UserID = $user['UserID'];
        $field->system = 1;
        $field->Created = date('Y-m-d H:i:s');
        $field->Modyfied = $field->Created;
        $field->save();

        /** @var \samson\activerecord\structure $structure Get system material structure */
        $structure = null;
        if (dbQuery('structure')->cond('Url', '__material')->exec($structure)) {
            $structure = array_shift($structure);
        }

        // Create relation between system material structure and gallery field
        $structureField = new structurefield(false);
        $structureField->FieldID = $field->FieldID;
        $structureField->StructureID = $structure->StructureID;
        $structureField->Active = 1;
        $structureField->Modified = date('Y-m-d H:i:s');
        $structureField->save();

        /** @var array $gallery Array of \samson\activerecord\gallery objects */
        $gallery = null;
        if (dbQuery('gallery')->exec($gallery)) {
            /** @var \samson\activerecord\gallery $image Set gallery as additional field */
            foreach ($gallery as $image) {
                // Create new materialfield for image and save it id in gallery table
                if (!dbQuery('materialfield')->cond('MaterialID', $image->MaterialID)->cond('FieldID', $field->FieldID)->first($materialField)) {
                    $materialField = new materialfield(false);
                    $materialField->MaterialID = $image->MaterialID;
                    $materialField->FieldID = $field->FieldID;
                }
                $materialField->Active = 1;
                $materialField->save();

                if (!dbQuery('structurematerial')->cond('MaterialID', $image->MaterialID)->cond('StructureID', $structure->StructureID)->first($structureMaterial)) {
                    $structureMaterial = new structurematerial(false);
                    $structureMaterial->MaterialID = $image->MaterialID;
                    $structureMaterial->StructureID = $structure->StructureID;
                    $structureMaterial->Modified = date('Y-m-d H:i:s');
                }
                $structureMaterial->Active = 1;
                $structureMaterial->save();

                $image->materialFieldId = $materialField->MaterialFieldID;
                $image->save();
            }
        }
    }

    /**
     * Add new key_value field to materialfield table, and also create index
     */
    public function migrate_24_to_25()
    {
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'materialfield` ADD `key_value` BIGINT NOT NULL DEFAULT 0 AFTER `MaterialID`');
        db()->query('ALTER TABLE  `' . dbMySQLConnector::$prefix . 'materialfield` ADD INDEX `key_value` (`key_value`)');
    }

    /**
     * Save old material identifiers
     */
    public function migrate_25_to_26()
    {
        /** @var array $fieldIds Array of material type fields */
        $fieldIds = array();
        // Fill the array
        dbQuery('field')->cond('Type', 6)->fields('FieldID', $fieldIds);
        /** @var array $materialFields Array of materialfields, which have type of filed material */
        $materialFields = dbQuery('materialfield')->cond('FieldID', $fieldIds)->exec();
        /** @var \samson\activerecord\materialfield $materialField */
        foreach ($materialFields as $materialField) {
            $materialField->key_value = $materialField->Value;
            $materialField->Value = '';
            $materialField->save();
        }
    }

    public function migrate_26_to_27()
    {
        db()->query('ALTER TABLE `user` CHANGE  `UserID`  `user_id` INT( 11 ) NOT NULL AUTO_INCREMENT');
        db()->query('ALTER TABLE `user` CHANGE  `FName`  `f_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
        db()->query('ALTER TABLE `user` CHANGE  `SName`  `s_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
        db()->query('ALTER TABLE `user` CHANGE  `TName`  `t_name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
        db()->query('ALTER TABLE `user` CHANGE  `Email`  `email` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL');
        db()->query('ALTER TABLE `user` CHANGE  `Created`  `created` DATETIME NOT NULL');
        db()->query('ALTER TABLE `user` CHANGE  `Modyfied`  `modified` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        db()->query('ALTER TABLE `user` CHANGE  `GroupID`  `group_id` INT( 11 ) NOT NULL');
        db()->query('ALTER TABLE `user` CHANGE  `Active`  `active` INT( 11 ) NOT NULL');
        db()->query('ALTER TABLE `user` DROP `LastLogin`');
        db()->query('ALTER TABLE `user` DROP `Password`');
        db()->query('ALTER TABLE `user` DROP `accessToken`');
        db()->query('ALTER TABLE `user` DROP `Online`');
    }

    public function migrate_27_to_28()
    {
        db()->query('ALTER TABLE  `material` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `user` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `structure` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `structurematerial` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `structurefield` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `materialfield` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `field` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `group` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `right` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `groupright` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `gallery` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `material` ENGINE = INNODB;');
        db()->query('ALTER TABLE  `structure_relation` ENGINE = INNODB;');


        db()->query('ALTER TABLE  `material` ADD INDEX (`parent_id`)');
        db()->query("ALTER TABLE  `material` CHANGE  `parent_id`  `parent_id` INT( 11 ) NULL DEFAULT  '0';");

        // Remove empty structurematerial to structure relations
        db()->query("DELETE FROM structurematerial WHERE structurematerialid in (select * from (SELECT sm.structurematerialid FROM `structurematerial` as sm left join structure as s on sm.structureid = s.structureID WHERE s.structureid is null) as p)");
        // Add cascade relation by structure
        db()->query("ALTER TABLE  `structurematerial` ADD FOREIGN KEY (  `StructureID` ) REFERENCES  `yourtour`.`structure` (`StructureID`) ON DELETE CASCADE ON UPDATE CASCADE ;");

        // Remove empty structurematerial to material relations
        db()->query("DELETE FROM structurematerial WHERE structurematerialid in (select * from (SELECT sm.structurematerialid FROM `structurematerial` as sm left join material as m on sm.materialid = m.materialid WHERE m.materialid is null) as p)");
        // Add cascade relation by material
        db()->query('ALTER TABLE  `structurematerial` ADD FOREIGN KEY (  `MaterialID` ) REFERENCES  `yourtour`.`material` (`MaterialID`) ON DELETE CASCADE ON UPDATE CASCADE ;');

        // Remove empty structure_relation to structure
        db()->query('DELETE FROM structure_relation WHERE structure_relation_id in (select * from (SELECT sm.structure_relation_id FROM `structure_relation` as sm left join structure as s on sm.child_id = s.structureid WHERE s.structureid is null) as p)');
        db()->query('DELETE FROM structure_relation WHERE structure_relation_id in (select * from (SELECT sm.structure_relation_id FROM `structure_relation` as sm left join structure as s on sm.parent_id = s.structureid WHERE s.structureid is null) as p)');
        // Add cascade relation by structure
        db()->query('ALTER TABLE  `structure_relation` ADD FOREIGN KEY (  `parent_id` ) REFERENCES  `yourtour`.`structure` (`StructureID`) ON DELETE CASCADE ON UPDATE CASCADE ;');
        db()->query('ALTER TABLE  `structure_relation` ADD FOREIGN KEY (  `child_id` ) REFERENCES  `yourtour`.`structure` (`StructureID`) ON DELETE CASCADE ON UPDATE CASCADE ;');

        // Remove empty materialfield relation to material
        db()->query('DELETE FROM materialfield WHERE materialfieldid in (select * from (SELECT mf.materialfieldid FROM `materialfield` as mf left join material as m on mf.materialid = m.materialid WHERE m.materialid is null) as p)');
        // Remove empty materialfield relation to field
        db()->query('DELETE FROM materialfield WHERE materialfieldid in (select * from (SELECT mf.materialfieldid FROM `materialfield` as mf left join field as s on mf.fieldid = s.fieldid WHERE s.fieldid is null) as p)');
        // Add cascade relation by material
        db()->query('ALTER TABLE  `materialfield` ADD FOREIGN KEY (  `MaterialID` ) REFERENCES  `yourtour`.`material` (`MaterialID`) ON DELETE CASCADE ON UPDATE CASCADE ;');
        // Add cascade relation by field
        db()->query('ALTER TABLE  `materialfield` ADD FOREIGN KEY (  `FieldID` ) REFERENCES  `yourtour`.`field` (`FieldID`) ON DELETE CASCADE ON UPDATE CASCADE ;');

        // Remove empty structurefield relation to structure
        db()->query('DELETE FROM structurefield WHERE structurefieldid in (select * from (SELECT sm.structurefieldid FROM `structurefield` as sm left join structure as s on sm.structureid = s.structureID WHERE s.structureid is null) as p)');
        // Remove empty structurefield relation to field
        db()->query('DELETE FROM structurefield WHERE structurefieldid in (select * from (SELECT sm.structurefieldid FROM `structurefield` as sm left join field as s on sm.fieldid = s.fieldid WHERE s.fieldid is null) as p)');
        db()->query("ALTER TABLE  `yourtour`.`structurefield` ADD INDEX  `structureid` (  `StructureID` ) COMMENT;");
        // Add cascade relation by structure
        db()->query('ALTER TABLE  `structurefield` ADD FOREIGN KEY (  `StructureID` ) REFERENCES  `yourtour`.`structure` (`StructureID`) ON DELETE CASCADE ON UPDATE CASCADE ;');
        // Add cascade relation by field
        db()->query('ALTER TABLE  `structurefield` ADD FOREIGN KEY (  `FieldID` ) REFERENCES  `yourtour`.`field` (`FieldID`) ON DELETE CASCADE ON UPDATE CASCADE ;');

        // Do the same with groupright table
        db()->query('DELETE FROM groupright WHERE grouprightid in (select * from (SELECT sm.grouprightid FROM `groupright` as sm left join `group` as s on sm.groupid = s.groupid WHERE s.groupid is null) as p)');
        db()->query('DELETE FROM groupright WHERE grouprightid in (select * from (SELECT sm.grouprightid FROM `groupright` as sm left join `right` as s on sm.rightid = s.rightid WHERE s.rightid is null) as p)');
        db()->query('ALTER TABLE  `groupright` ADD FOREIGN KEY (  `GroupID` ) REFERENCES  `yourtour`.`group` (`GroupID`) ON DELETE CASCADE ON UPDATE CASCADE ;');
        db()->query('ALTER TABLE  `groupright` ADD FOREIGN KEY (  `RightID` ) REFERENCES  `yourtour`.`right` (`RightID`) ON DELETE CASCADE ON UPDATE CASCADE ;');

        db()->query('DROP TABLE `related_materials`;');
    }

    public function materialColumnToField($column, $structure)
    {
        // Find first user
        $user = null;
        if (dbQuery('user')->first($user)) {

        }

        // Create structure for all materials
        $db_structure = null;
        if (!dbQuery('structure')->Url('__' . $structure)->Active(1)->first($db_structure)) {
            $db_structure = new \samson\activerecord\structure(false);
            $db_structure->Name = $structure;
            $db_structure->Url = '__' . $structure;
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
            trace('Found materials:' . sizeof($db_materials));
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

        db()->query('ALTER TABLE  `material` DROP  `' . $column . '`');
    }

    /**
     * Get CMSMaterial by selector
     * Field to search for can be specified, by default search if performed by URL field
     * Function supports finding material by own fields and by any additional fields
     *
     * @param string $selector Value of CMSMaterial to search
     * @param string $field Field name for searching
     * @return CMSMaterial Instance of CMSMaterial on successfull search
     */
    public function & material($selector, $field = 'Url')
    {
        $db_cmsmat = null;

        // If id passed switch to real table column name
        if ($field == 'id') $field = 'MaterialID';

        // Build classname with PHP < 5.3 compatibility
        $classname = ns_classname('cmsmaterial', 'samson\cms');

        // If instance of CMSMaterial passed - just return it
        if ($selector instanceof $classname) return $selector;
        // Try to search activerecord instances cache by selector
        else if (isset(dbRecord::$instances[$classname][$selector])) $db_cmsmat = &dbRecord::$instances[$classname][$selector];
        // Try to load from memory cache
        //else if( CacheTable::ifget( $selector, $db_cmsmat ) );
        // Perform request to database
        else {
            // Get material	by field
            $db_cmsmat = CMSMaterial::get(array($field, $selector), NULL, 0, 1);

            // If we have found material - get the first one
            if (is_array($db_cmsmat) && sizeof($db_cmsmat)) $db_cmsmat = array_shift($db_cmsmat);
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
        if (!isset($selector)) return $cmsnav;

        // If id passed switch to real table column name
        if ($field == 'id') $field = 'StructureID';

        // Build classname with PHP < 5.3 compatibility
        $classname = \samson\core\AutoLoader::className('CMSNav', 'samson\cms');

        // If instance of CMSNav passed - just return it
        if (is_a($selector, $classname)) return $selector;
        // Try to search activerecord instances cache by selector
        else if (isset(dbRecord::$instances[$classname][$selector])) {
            $cmsnav = &dbRecord::$instances[$classname][$selector];
        } // Perform request to database
        else if (dbQuery($classname)
            ->cond('Active', 1)
            ->cond($field, $selector)
            ->join('children_relations')
            ->join('children', '\samson\cms\CMSNav')
            ->join('parents_relations')
            ->join('parents', '\samson\cms\CMSNav')
            ->first($cmsnav)
        ) {
            $cmsnav->prepare();
        }

        return $cmsnav;
    }

    /**
     * Perform request to get CNSMaterials by CMSNav
     * @param mixed $selector CMSNav selector
     * @param string $field CMSNav field name for searching
     * @param string $handler External handler
     * @return array
     */
    public function & navmaterials($selector, $field = 'Url', $handler = null)
    {
        $result = array();

        // Find CMSNav
        if (null !== ($db_nav = $this->navigation($selector, $field))) {
            // Get material ids from structure materials records
            $ids = array();
            if (dbQuery('samson\cms\CMSNavMaterial')->cond('StructureID', $db_nav->id)->fields('MaterialID', $ids)) {
                // Create material db query
                $q = cmsquery()->id($ids);

                // Set ecternal query handler
                if (isset($handler)) $q->handler($handler);

                // Perform DB request and get materials
                $result = $q->exec();
            }
        }

        return $result;
    }

    /** @see \samson\core\CompressableExternalModule::afterCompress() */
    public function afterCompress(& $obj = null, array & $code = null)
    {
        // Fill additional fields data to material db request data for automatic altering material request
        self::$fields = array();

        $t_name = '_mf';

        // Save original material attributes
        self::$materialAttributes = &CMSMaterial::$_attributes;

        // Copy original material table attributes
        CMSMaterial::$_attributes = \samson\activerecord\material::$_attributes;
        CMSMaterial::$_sql_select = \samson\activerecord\material::$_sql_select;
        CMSMaterial::$_sql_from = \samson\activerecord\material::$_sql_from;
        CMSMaterial::$_own_group = \samson\activerecord\material::$_own_group;
        CMSMaterial::$_map = \samson\activerecord\material::$_map;

        // Perform db query to get all possible material fields
        if (dbQuery('field')->Active(1)->Name('', dbRelation::NOT_EQUAL)->exec($this->material_fields)) foreach ($this->material_fields as $db_field) {
            // Add additional field localization condition
            if ($db_field->local == 1) $equal = '((' . $t_name . '.FieldID = ' . $db_field->id . ')&&(' . $t_name . ".locale = '" . locale() . "'))";
            else $equal = '((' . $t_name . '.FieldID = ' . $db_field->id . ')&&(' . $t_name . ".locale = ''))";

            // Define field value DB column for storing data
            $v_col = 'Value';
            // We must get data from other column for this type of field
            if ($db_field->Type == 7 || $db_field->Type == 3 || $db_field->Type == 10) {
                $v_col = 'numeric_value';
            } else if ($db_field->Type == 6) {
                $v_col = 'key_value';
            }

            // Save additional field
            self::$fields[$db_field->Name] = "\n" . ' MAX(IF(' . $equal . ',' . $t_name . '.`' . $v_col . '`, NULL)) as `' . $db_field->Name . '`';

            // Set additional object metadata
            CMSMaterial::$_attributes[$db_field->Name] = $db_field->Name;
            CMSMaterial::$_map[$db_field->Name] = dbMySQLConnector::$prefix . 'material.' . $db_field->Name;
        }

        // Set additional object metadata
        CMSMaterial::$_sql_select['this'] = ' STRAIGHT_JOIN ' . CMSMaterial::$_sql_select['this'];
        if (sizeof(self::$fields)) {
            CMSMaterial::$_sql_select['this'] .= ',' . implode(',', self::$fields);
        }
        CMSMaterial::$_sql_from['this'] .= "\n" . 'LEFT JOIN ' . dbMySQLConnector::$prefix . 'materialfield as ' . $t_name . ' on ' . dbMySQLConnector::$prefix . 'material.MaterialID = ' . $t_name . '.MaterialID';
        CMSMaterial::$_own_group[] = dbMySQLConnector::$prefix . 'material.MaterialID';
    }

    /** @see \samson\core\ExternalModule::init() */
    public function init(array $params = array())
    {
        // Change static class data
        $this->afterCompress();

        // Create cache collection
        dbRecord::$instances["samson\cms\CMSMaterial"] = array();
    }
}
