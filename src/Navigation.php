<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 07.08.14 at 17:11
 */
namespace samson\cms;

use samson\activerecord\structure;

/**
 * SamsonCMS Navigation element
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2014 SamsonOS
 */
class Navigation extends structure implements \Iterator
{
    /** @var string Navigation string identifier */
    public $Url;

    /** @var \samson\cms\Navigation[] Collection of child items */
    public $children = array();

    /** @var array WTF?? */
    public $parentsnav = array();

    /** @var bool WTF??? */
    protected $base = false;

    /**
     * Override standard view passing
     * @param string $prefix Prefix
     * @param array $restricted Collection of ignored entity fields
     * @return array Filled collection of key => values for view
     */
    public function toView($prefix = '', array $restricted = array())
    {
        return parent::toView($prefix, $restricted = array('parent','parents','children'));
    }

    /**
     * Material query injection
     * @param \samson\activerecord\dbQuery $query Query object
     */
    public function materialsHandlers(&$query)
    {
        $query->join('gallery');
    }

    /**
     * Get all related materials
     * @return \samson\cms\CMSMaterial[] Collection of related materials
     */
    public function & materials()
    {
        /** @var \samson\cms\Material[] $materials Get related materials collection */
        $materials = array();
        // Perform generic material retrieval
        if (CMS::getMaterialsByStructures(
            array($this->id),
            $materials,
            'samson\cms\CMSMaterial',
            null,
            array(),
            array($this, 'materialsHandlers'))) {
            // Handle
        }

        return $materials;
    }

    /**
     * Get all related fields
     * @return \samson\cms\Field[] Collection of related fields
     */
    public function & fields()
    {
        // Prepare db request to get related fields
        $fieldIDs = dbQuery('structurefield')
            ->cond('StructureID', $this->id)
            ->cond('Active', 1)
            ->fieldsNew('FieldID');

        /** @var \samson\cms\NavigationField[] $fields Get collection of related navigation fields */
        $fields = array();
        if (sizeof($fieldIDs)) {
            dbQuery('samson\cms\Field')->id($fieldIDs)->exec($fields);
        }

        return $fields;
    }

    /**
     * Get default Material object
     * @return \samson\cms\Material|bool Default Material object, otherwise false
     */
    public function def()
    {
        // If this naviagtion has default material identifier specified
        if (isset($this->MaterialID) && $this->MaterialID > 0) {
            // Perform db query to get this material
            return dbQuery('samson\cms\Material')->id($this->MaterialID)->first();
        }

        return false;
    }

    /**
     * Get all children navigation elements default material object.
     * This approach increases performance on large navigation tree branches.
     */
    public function childrenDef()
    {
        // Gather all default materials
        $defaultMaterialIds =  array();

        foreach ($this->children() as $child) {
            $defaultMaterialIds[] = $child->MaterialID;
        }

        // Perform database query
        return dbQuery('samson\cms\CMSMaterial')->cond('MaterialID', $defaultMaterialIds)->exec();
    }

    // TODO: Functions lower to this line should be rewritten by kotenko@samsonos.com

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

    /**
     * WTF?
     */
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
        return $this->children;
    }

    public function rewind()
    {
        $this->base();
        reset( $this->children );
    }

    public function next()
    {
        $this->base();
        return next( $this->children );
    }

    public function current()
    {
        $this->base();
        return current( $this->children );
    }
    public function key()
    {
        $this->base();
        return key( $this->children );
    }

    public function valid()
    {
        $this->base();
        $key = key( $this->children );
        return ($key !== null && $key !== false);
    }
}
