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
                // Store all retrieved navigation elements as navigation collection filter
                $this->navigation[] = $navigation;
            }
        }

        // Chaining
        return $this;
    }

    /**
     * Filter collection using additional field entity.
     *
     * @param string|integer $field Additional field identifier or name
     * @param mixed $value Additional field value for filtering
     * @param string $relation Additional field relation for filtering
     * @return self Chaining
     */
    public function field($field, $value, $relation = dbRelation::EQUAL)
    {
        // Do not allow empty strings
        if(isset($field{0})) {
            // Create id or URL condition
            $idOrUrl = new Condition('OR');
            $idOrUrl->add('FieldID', $field)->add('Name', $field);

            /** @var \samson\activerecord\field $navigation */
            $navigation = null;
            if (dbQuery('field')->cond($idOrUrl)->first($field)) {
                // Store retrieved field element and its value as field collection filter
                $this->field[] = array($field, $value, $relation);
            }
        }

        // Chaining
        return $this;
    }
} 