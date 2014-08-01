<?php
/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 01.08.14 at 11:07
 */

/** Automatic migration to new CMS table structure */
function migrate_1_to_2()
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
function migrate_2_to_3()
{
    elapsed('Adding `locale` field into `structure` table');
    db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'structure` ADD  `locale` VARCHAR( 10 ) NOT NULL AFTER  `Name` ;');
}

/** Automatic migration to new CMS table structure */
function migrate_3_to_4()
{
    elapsed('Adding `locale` field into `materialfield` table');
    db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'materialfield` ADD  `locale` VARCHAR( 10 ) NOT NULL AFTER  `numeric_value` ;');
    elapsed('Adding `local` field into `field` table');
    db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'field` ADD  `local` int( 10 ) NOT NULL AFTER  `Type` ;');
}

function migrate_4_to_5()
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

function migrate_5_to_6()
{
    // Convert all old "date" fields to numeric for fixing db requests
    if (dbQuery('field')->Type(3)->fields('id',$fields)) {
        foreach (dbQuery('materialfield')->FieldID($fields)->exec() as $mf) {
            $mf->numeric_value = strtotime($mf->Value);
            $mf->save();
        }
    }

}

function migrate_6_to_7()
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

function migrate_7_to_8()
{
    elapsed('Adding `StructureID` field into `material` table');
    db()->simple_query('ALTER TABLE  `'.dbMySQLConnector::$prefix.'material` ADD  `structure_id` INT( 255 ) NOT NULL AFTER  `Active`');
}
