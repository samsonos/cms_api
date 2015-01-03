<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 26.12.2014
 * Time: 16:10
 */
namespace samsonos\cms\collection;

use samson\pager\Pager;

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
     * Pager db request handler
     * @param \samson\activerecord\dbQuery $query
     */
    public function pagerInjection(&$query)
    {
        // Create count request to count pagination
        $countQuery = clone $query;
        $this->pager->update($countQuery->count());

        // Set current page query limits
        $query->limit($this->pager->start, $this->pager->end);
    }

    /**
     * Constructor
     * @param \samson\core\IViewable $renderer View render object
     * @param int $page Current page number
     */
    public function __construct($renderer, $page = 1)
    {
        // Create pagination
        $this->pager = new Pager($page, $this->pageSize);

        // Set pager db query injection
        $this->entityHandler(array($this, 'pagerInjection'));

        // Call parents
        parent::__construct($renderer);
    }
}
