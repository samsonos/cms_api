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
    public static $top;

    public $parent = NULL;

    public $parentsnav = array();


    protected $parents = array();


    public $children = array();

    /** @var string Navigation string identifier */
    public $Url;

    protected $url_base = '';

    protected $level = 0;

    protected $base = false;

    public function tree(){
        $tree = array();
        $s_rs = array();
        //Get all structure telations
        if(dbQuery('structure_relation')->exec($s_r)){

        }
    }


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

        if( dbQuery('samson\cms\CMSNavField')->StructureID($this->id)->Active(1)->exec( $db_fields ) )
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

    // TODO: Functions lower to this line should be rewritten by kotenko@samsonos.com

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
