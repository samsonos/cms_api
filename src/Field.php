<?php
namespace samson\cms;

/**
 * SamsonCMS field table class
 * @package samson\cms
 */
class Field extends \samson\activerecord\field
{
  /**
     * Find field database record by identifier
     * @param string $identifier Field identifier
     * @param self $return Return self instance
     * @return bool|null
     */
    public static function byID($identifier, self & $return = null )
    {
        // Get field record by identiifer column
        $return = static::oneByColumn(new dbQuery(), self::$_primary, $identifier);

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? $return == null : $return;
    }

    /**
     * Find field database record by name
     * @param string $identifier Field identifier
     * @param self $return Return self instance
     * @return bool|null
     */
    public static function byName($identifier, self & $return = null )
    {
        // Get field record by identiifer column
        $return = static::oneByColumn(new dbQuery(), self::$_primary, 'Name');

        // If only one argument is passed - return null, otherwise bool
        return func_num_args() > 1 ? $return == null : $return;
    }
}
