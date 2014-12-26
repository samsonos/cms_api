<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 26.12.2014
 * Time: 16:22
 */

namespace samson\cms;


use samson\activerecord\Condition;
use samson\activerecord\dbRelation;

class CollectionQuery
{
    /** @var array Collection for current filtered material identifiers */
    protected $materialIDs = array();

    /** @var array Collection of navigation filters */
    protected $navigation = array();

    /** @var array Collection of field filters */
    protected $field = array();

    /**
     * Filter collection using navigation entity or collection of them.
     * If collection of navigation Url or Ids is passed then this group will be
     * applied as single navigation filter to retrieve materials.
     *
     * @param string|integer|array $navigation Navigation URL or identifier for filtering
     * @return self Chaining
     */
    public function navigation($navigation)
    {
        // Do not allow empty strings
        if(isset($navigation{0})) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('StructureID', $navigation)->add('Url', $navigation);

            /** @var \samson\activerecord\structure $navigation */
            $navigation = null;
            if (dbQuery('structure')->cond($idOrUrl)->exec($navigation)) {
                // Store all retrieved navigation elements ass navigation collection filter
                $this->navigation[] = $navigation;
            }
        }

        // Chaining
        return $this;
    }

    public function field($field, $value, $relation = dbRelation::EQUAL)
    {

    }
} 