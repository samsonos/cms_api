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
    /** @var callable External handler for rendering material block */
    protected $indexRenderer;

    /** @var callable External handler for rendering material item in block */
    protected $itemRenderer;

    /**
     * Generic collection constructor
     */
    public function __construct($indexRenderer, $itemRenderer)
    {
        $this->indexRenderer = $indexRenderer;

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
            $html = call_user_func_array($this->indexRenderer, array(&$html));
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