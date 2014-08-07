<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 07.08.14 at 16:20
 */
 namespace samson\cms;
 use samson\activerecord\dbRelation;

 /**
 * Generic class for generating requests to SamsonCMS tables
 * @author Vitaly Egorov <egorov@samsonos.com>
 * @copyright 2014 SamsonOS
 * @version 0.0.1
 */
class Query extends \samson\activerecord\Query
{
    /** @var array Collection of all possible material additional fields */
    public static $fields = array();

    /** @var array Collection of fields that will be used in a current query */
    protected $queryFields = array();

    /** @var string Material table name for joining */
    protected $joinTable = 'manymaterials';

    /**
     * Overloaded standard query execution method
     * @see \samson\activerecord\Query::execute()
     */
    protected function & execute( & $result = null, $r_type = false, $limit = null, $handler = null, $handler_args = array() )
    {
        // Add additional material fields


        // Call standard execution logic
        return parent::execute($result, $r_type, $limit, $handler, $handler_args);
    }

    /**
     * Add CMSMaterial field to query to get this field filled in an object
     * as its native property.
     *
     * This function accepts as as many as possible arguments, every argument
     * must be a valid field name and will be checked in db table schema if its
     * valid.
     *
     * This function can be called several times as just merges the $queryFields array,
     * so it won't duplicate the fields in current query.
     *
     * @return \samson\cms\Query Chaining
     */
    public function fields()
    {
        // Get all function arguments
        $fields = func_get_args();

        // Convert arguments array to keys and perform keys match from db table scheme
        $this->queryFields = array_merge($this->queryFields, array_intersect_key(self::$fields, array_flip($fields)));
    }

    /**
     * Set [active] material field condition
     * @param integer   $value      Active parameter value for query
     * @param string    $condition  (optional) Active parameter condition for query
     *
     */
    public function Active($value, $condition = dbRelation::EQUAL)
    {
        return $this->cond('Active', $value, $condition);
    }

    /**
     * Set [published] material field condition
     * @param integer   $value      Published parameter value for query
     * @param string    $condition  (optional) Published parameter condition for query
     *
     */
    public function Published($value, $condition = dbRelation::EQUAL)
    {
        return $this->cond('Published', $value, $condition);
    }

    /**
     * Set [Draft] material field condition
     * @param integer   $value      Draft parameter value for query
     * @param string    $condition  (optional) Draft parameter condition for query
     *
     */
    public function Draft($value, $condition = dbRelation::EQUAL)
    {
        return $this->cond('Draft', $value, $condition);
    }

    /**
     * Create instance of CMS Query
     * @param string $class     CMS material class name for creating result materials
     * @param string $joinTable Name of material joining table to use in query
     */
    public function __construct($class = 'samson\cms\cmsmaterial', $joinTable = 'manymaterials')
    {
        $this->joinTable = $joinTable;

        // Check if we working with material table class
        if (!is_subclass_of($class, '\samson\activerecord\material')) {
            return e('Cannot create CMS Query: Class ## is not ancestor of \samson\activerecord\material class', E_SAMSON_CMS_ERROR, $class);
        }

        /* Always create query to start from structure table as it cover most of the cases */
        parent::__construct('samson\cms\nav');

        // Create db request
        $this
            ->join($class)                  // This class must be ancestor of \samson\activerecord\material
            ->join('samson\cms\cmsgallery') // Always join gallery
            ->join('user')                  // Always join user
            ->join('materialfield')         // Always join fields
        ;
    }
}
 