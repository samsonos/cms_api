<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 18.10.2014
 * Time: 11:44
 */
namespace samson\cms;

use samson\core\iModuleViewable;

/**
 * This class is a generic approach for rendering catalogs and lists
 * of materials, it should be extended to match needs of specific
 * project.
 *
 * @package samson\cms
 * @deprecated Use GenericCollection
 */
abstract class GenericMaterialCollection implements \Iterator, iModuleViewable
{
    /** @var Material[] Collection of products */
    protected $collection = array();

    /**
     * Render material collection block
     * @return string Rendered material collection block
     */
    abstract public function render();

    /**
     * Fill collection with items
     * @return Material[] Collection of product items
     */
    abstract public function fill();

    /**
     * Render products collection block
     */
    public function toView($prefix = null, array $restricted = array())
    {
        return array($prefix.'html' => $this->render());
    }

    /**
     * Generic collection constructor
     */
    public function __construct()
    {
        // Call internal method to fill collection
        $this->collection = call_user_func_array(array($this, 'fill'), func_get_args());
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
