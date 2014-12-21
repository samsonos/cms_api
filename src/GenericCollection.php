<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 18.10.2014
 * Time: 11:44
 */
namespace samson\cms;

use samson\core\IViewSettable;

/**
 * This class is a generic approach for rendering catalogs and lists,
 * it should be extended to match needs of specific project.
 *
 * @package samson\cms
 */
abstract class GenericCollection implements \Iterator, IViewSettable
{
    /** @var array Collection */
    protected $collection = array();

    /** @var string Block view file */
    protected $indexView = 'www/index';

    /** @var string Item view file */
    protected $itemView = 'www/item';

    /** @var string Empty view file */
    protected $emptyView = 'www/empty';

    /** @var \samson\core\IViewable View render object */
    protected $renderer;

    /**
     * Fill collection with items
     * @return array Collection of items
     */
    abstract public function fill();

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
                // Render item views
                $html .= $this->renderer
                    ->view($this->itemView)
                    ->set($item, 'item')
                    ->output();
            }
            // Render block view
            $html = $this->renderer
                ->view($this->indexView)
                ->set('items', $html)
                ->output();

        } elseif (isset($this->emptyView{0})) { // Render empty view
            $html = $this->renderer->view($this->emptyView)->output();
        }

        return $html;
    }

    /**
     * Generate collection of view variables, prefixed if needed, that should be passed to
     * view context.
     *
     * @param string $prefix Prefix to be added to all keys in returned data collection
     * @return array Collection(key => value) of data for view context
     */
    public function toView($prefix = '')
    {
        return array(
            $prefix.'html' => $this->render()
        );
    }

    /**
     * Generic collection constructor
     * @var \samson\core\IViewable View render object
     */
    public function __construct($renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return current($this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->collection);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        $key = key($this->collection);

        return ($key !== null && $key !== false);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->collection);
    }
}
