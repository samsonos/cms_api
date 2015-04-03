<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 26.12.2014
 * Time: 16:10
 */
namespace samsonos\cms\collection;

use samsonframework\core\RenderInterface;
use samsonframework\pager\PagerInterface;
use samsonframework\orm\QueryInterface;

/**
 * Generic SamsonCMS entities collection with pages
 * @package samsonos\cms\collection
 * @author Egorov Vitaly <egorov@samsonos.com>
 */
abstract class Paged extends Filtered
{
    /** @var int Amount of tours at one page */
    protected $pageSize = 15;

    /** @var  \samson\pager\Pager Pagination */
    protected $pager;

    /**
     * Render products collection block
     * @param string $prefix Prefix for view variables
     * @param array $restricted Collection of ignored keys
     * @return array Collection key => value
     */
    public function toView($prefix = null, array $restricted = array())
    {
        // Render pager and collection
        return array(
            $prefix.'html' => $this->render(),
            $prefix.'pager' => $this->pager->toHTML()
        );
    }

    /**
     * Pager id handler
     * @param array Array of material identifiers
     */
    public function pagerIDInjection(& $materialIds)
    {
        // Create count request to count pagination
        $this->pager->update(sizeof($materialIds));

        // Cut only needed materials identifiers from array
        $materialIds = array_slice($materialIds, $this->pager->start, $this->pager->end);
    }

    /**
     * Constructor
     * @param RenderInterface $renderer View render object
     * @param QueryInterface $query Query object
     * @param PagerInterface $pager Pager instance
     */
    public function __construct(RenderInterface $renderer, QueryInterface $query, PagerInterface $pager)
    {
        // Set pager
        $this->pager = $pager;

        // Set pager id injection
        $this->handler(array($this, 'pagerIDInjection'));

        // Call parents
        parent::__construct($renderer, $query);
    }
}
