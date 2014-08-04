<?php
namespace samson\cms;

use samson\activerecord\dbMySQLConnector;
use samson\activerecord\dbRelation;

/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 01.08.14 at 13:30
 */

class ClosureTable
{
    /** @var string Database name */
    private $name = 'samson\activerecord\\';
    /** @var string Entity Name  */
    private $entity = null;

    /**
     * Constructor with parameters
     * It sets the name of table and entity to work with
     * @param string $name Name of table
     * @param string $entity Entity type
     */
    public function __construct($name, $entity)
    {
        $this->name = $this->name.$name;
        $this->entity = $entity;
    }

    /**
     * Create table in database by specified name
     * @param string $name Name of table
     */
    public static function prepare($name)
    {
        /** @var string $sqlTable SQL query to create table */
        $sqlTable = "CREATE TABLE IF NOT EXISTS `".dbMySQLConnector::$prefix.$name."` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `parent_id` int(11) NOT NULL,
		  `child_id` int(11) NOT NULL,
		  `level` int(11) NOT NULL,
		  `info` varchar(50) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `parent_id` (`parent_id`),
		  KEY `child_id` (`child_id`),
		  KEY `level` (`level`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;";
        db()->simple_query($sqlTable);
    }

    /**
     * Returns Identifier
     * @param mixed $obj Object to get identifier or identifier by itself
     * @return mixed identifier
     */
    private function isObject($obj)
    {
        /** @var int $value Return value */
        $value = null;
        // Checks if input parameter is object
        if (is_object($obj)) {
            $value = $obj->id;
        } else {
            $value = $obj;
        }
        return $value;
    }

    /**
     * Adds element to parent node
     * @param mixed $obj Object or identifier to add
     * @param mixed $parentObj Parent object or identifier
     */
    public function add($obj, $parentObj)
    {
        /** @var int $childId Input object identifier */
        $childId = $this->isObject($obj);
        /** @var int $parentId Parent identifier */
        $parentId = $this->isObject($parentObj);
        /** @var array $parents Array of parent Nodes */
        $parents = null;

        dbQuery($this->name)->cond('child_id', $parentId)->exec($parents);
        foreach ($parents as $parent) {
            $temp = new $this->name();
            $temp->parent_id = $parent->parent_id;
            $temp->child_id = $childId;
            $temp->level = $parent->level + 1;
            $temp->save();
        }
        $temp = new $this->name();
        $temp->parent_id = $parentId;
        $temp->child_id = $childId;
        $temp->level = 1;
        $temp->save();
    }

    /**
     * Deletes all entries by id
     * @param mixed $obj Object or id to delete
     */
    public function delete($obj)
    {
        /** @var int $parentId Input object Identifier */
        $parentId = $this->isObject($obj);
        /** @var int[] $children Array of child identifiers */
        $children = null;
        /** @var string $name Short name of table */
        $name = substr($this->name, 20);

        dbQuery($this->name)->cond('parent_id', $parentId)->cond('level', 1)->fields('child_id', $children);
        db()->simple_query('DELETE FROM `'.$name.'` WHERE parent_id ='.$parentId.' OR child_id='.$parentId);
        foreach ($children as $child) {
            $this->delete($child);
        }
    }

    /**
     * Moves branch of elements to other node
     * @param mixed $obj Branch root element
     * @param mixed $parentObj Existing node
     */
    public function moveTo($obj, $parentObj)
    {
        /** @var int $childId Input object Identifier */
        $id = $this->isObject($obj);
        $newParentId = $this->isObject($parentObj);
        $children = null;
        $name = substr($this->name, 20);

        db()->simple_query('DELETE FROM `'.$name.'` WHERE child_id='.$id);
        dbQuery($this->name)->cond('parent_id', $id)->cond('level', 1)->fields('child_id', $children);
        $this->add($id, $newParentId);
        foreach ($children as $child) {
            $this->moveTo($child, $id);
        }
    }

    /**
     * Gets the list of children elements on specified level
     * @param mixed $obj Parent object
     * @param int $level Level on which child elements will be found
     * @return array Array of child elements
     */
    public function getChildren($obj, $level)
    {
        /** @var int $parentId Input object Identifier */
        $parentId = $this->isObject($obj);
        $children = null;
        $childrenEntities = null;

        dbQuery($this->name)->cond('parent_id', $parentId)->cond('level', $level)->fields('child_id', $children);
        $childrenEntities = dbQuery($this->entity)->cond('id', $children);
        return $childrenEntities;
    }

    /**
     * Gets the list of parent elements on specified level
     * If level is not set method will return all parent elements till the root
     * @param mixed $obj Child object or id
     * @param int $level Level on which parent elements will be found, if it is not set consider root element level
     * @return mixed Returns array of arrays of parent elements
     */
    public function getParents($obj, $level = null)
    {
        /** @var int $childId Identifier of input object */
        $childId = $this->isObject($obj);
        /** @var string[] $parents Collection of parent elements identifiers */
        $parents = null;
        /** @var \samson\activerecord\dbRecord $child  */
        $child = null;
        // Gets object by Identifier
        if (dbQuery($this->entity)->cond('id', $childId)->first($child)) {
            // Gets child elements of current object
            if (dbQuery($this->name)->cond('child_id', $childId)->cond('level', 1)->fields('parent_id', $parents)) {
                // Checks current level
                if ($level == null || $level > 0) {
                    if ($level > 0) {
                        $level = $level - 1;
                    }
                    foreach ($parents as $parent) {
                        $child->parents[] = $this->getParents($parent, $level);
                    }
                }
            }
            return $child;
        }
        return null;
    }
}
