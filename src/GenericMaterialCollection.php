<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 18.10.2014
 * Time: 18:02
 */

namespace samson\cms;

/**
 * Commonly used MaterialCollection implementation
 * @package samson\cms
 */
class GenericMaterialCollection extends MaterialCollection
{
    /** @var string Path to collection block view */
    protected $indexView;

    /** @var callable External handler for rendering material item in block */
    protected $itemRenderer;

    /**
     * Generic collection constructor
     */
    public function __construct($indexView, $itemRenderer)
    {
        $this->indexView = $indexView;

        $this->itemRenderer = $itemRenderer;

        // Call parent constructor
        parent::__construct();
    }

    /**
     * Render material collection block
     * @return string Rendered material collection block
     */
    public function render()
    {
        $html = '';

        // Do not render block if there is no items
        if (sizeof($this->collection)) {
            // Render all block items
            foreach ($this->collection as &$item) {
                // Call external block item renderer and pass item to it
                $html .= call_user_func_array($this->itemRenderer, array(&$item));
            }
            // Render block view
            $html = m()->view($this->indexView)->items($html)->output();
        }

        return $html;
    }

    /**
     * Fill collection with items
     * @return Material[] Collection of product items
     */
    public function fill(){
        return $this->collection;
    }
} 