<?php namespace samson\activerecord;
/**
 * Класс для работы с таблицей БД "cms_version"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class cms_version extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "cms_version";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "version"=>"version",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "version"=>"version",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`cms_version`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "cms_version.version",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "version"=>"varchar",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "version"=>"cms_version.version",
    );
    public $version = "";
    /** Создать экземпляр класса cms_version */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "cms_version" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["cms_version"] = array();
/**
 * Класс для работы с таблицей БД "field"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class field extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "field";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "FieldID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "FieldID"=>"FieldID",
        "ParentID"=>"ParentID",
        "priority"=>"priority",
        "Name"=>"Name",
        "Type"=>"Type",
        "local"=>"local",
        "filtered"=>"filtered",
        "Value"=>"Value",
        "Description"=>"Description",
        "UserID"=>"UserID",
        "Created"=>"Created",
        "Modyfied"=>"Modyfied",
        "Active"=>"Active",
        "system"=>"system",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "FieldID"=>"FieldID",
        "ParentID"=>"ParentID",
        "priority"=>"priority",
        "Name"=>"Name",
        "Type"=>"Type",
        "local"=>"local",
        "filtered"=>"filtered",
        "Value"=>"Value",
        "Description"=>"Description",
        "UserID"=>"UserID",
        "Created"=>"Created",
        "Modyfied"=>"Modyfied",
        "Active"=>"Active",
        "system"=>"system",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`field`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "field.FieldID,
field.ParentID,
field.priority,
field.Name,
field.Type,
field.local,
field.filtered,
field.Value,
field.Description,
field.UserID,
field.Created,
field.Modyfied,
field.Active,
field.system",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "FieldID"=>"int",
        "ParentID"=>"int",
        "priority"=>"int",
        "Name"=>"varchar",
        "Type"=>"int",
        "local"=>"int",
        "filtered"=>"int",
        "Value"=>"text",
        "Description"=>"text",
        "UserID"=>"int",
        "Created"=>"datetime",
        "Modyfied"=>"timestamp",
        "Active"=>"int",
        "system"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "UserID",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "FieldID"=>"field.FieldID",
        "ParentID"=>"field.ParentID",
        "priority"=>"field.priority",
        "Name"=>"field.Name",
        "Type"=>"field.Type",
        "local"=>"field.local",
        "filtered"=>"field.filtered",
        "Value"=>"field.Value",
        "Description"=>"field.Description",
        "UserID"=>"field.UserID",
        "Created"=>"field.Created",
        "Modyfied"=>"field.Modyfied",
        "Active"=>"field.Active",
        "system"=>"field.system",
    );
    public $FieldID = "";
    public $ParentID = "";
    public $priority = "";
    public $Name = "";
    public $Type = "";
    public $local = "";
    public $filtered = "";
    public $Value = "";
    public $Description = "";
    public $UserID = "";
    public $Created = "";
    public $Modyfied = "";
    public $Active = "";
    public $system = "";
    /** Создать экземпляр класса field */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "field" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["field"] = array();
/**
 * Класс для работы с таблицей БД "filter"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class filter extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "filter";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "filter_id";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "filter_id"=>"filter_id",
        "field_id"=>"field_id",
        "value"=>"value",
        "locale"=>"locale",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "filter_id"=>"filter_id",
        "field_id"=>"field_id",
        "value"=>"value",
        "locale"=>"locale",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`filter`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "filter.filter_id,
filter.field_id,
filter.value,
filter.locale",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "filter_id"=>"int",
        "field_id"=>"int",
        "value"=>"varchar",
        "locale"=>"varchar",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "filter_id"=>"filter.filter_id",
        "field_id"=>"filter.field_id",
        "value"=>"filter.value",
        "locale"=>"filter.locale",
    );
    public $filter_id = "";
    public $field_id = "";
    public $value = "";
    public $locale = "";
    /** Создать экземпляр класса filter */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "filter" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["filter"] = array();
/**
 * Класс для работы с таблицей БД "gallery"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class gallery extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "gallery";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "PhotoID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "PhotoID"=>"PhotoID",
        "MaterialID"=>"MaterialID",
        "materialFieldId"=>"materialFieldId",
        "priority"=>"priority",
        "Path"=>"Path",
        "Src"=>"Src",
        "size"=>"size",
        "Loaded"=>"Loaded",
        "Description"=>"Description",
        "Name"=>"Name",
        "Active"=>"Active",
        "test5"=>"test5",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "PhotoID"=>"PhotoID",
        "MaterialID"=>"MaterialID",
        "materialFieldId"=>"materialFieldId",
        "priority"=>"priority",
        "Path"=>"Path",
        "Src"=>"Src",
        "size"=>"size",
        "Loaded"=>"Loaded",
        "Description"=>"Description",
        "Name"=>"Name",
        "Active"=>"Active",
        "test5"=>"test5",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`gallery`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "gallery.PhotoID,
gallery.MaterialID,
gallery.materialFieldId,
gallery.priority,
gallery.Path,
gallery.Src,
gallery.size,
gallery.Loaded,
gallery.Description,
gallery.Name,
gallery.Active,
gallery.test5",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "PhotoID"=>"int",
        "MaterialID"=>"int",
        "materialFieldId"=>"int",
        "priority"=>"int",
        "Path"=>"varchar",
        "Src"=>"varchar",
        "size"=>"int",
        "Loaded"=>"timestamp",
        "Description"=>"text",
        "Name"=>"varchar",
        "Active"=>"int",
        "test5"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "MaterialID",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "PhotoID"=>"gallery.PhotoID",
        "MaterialID"=>"gallery.MaterialID",
        "materialFieldId"=>"gallery.materialFieldId",
        "priority"=>"gallery.priority",
        "Path"=>"gallery.Path",
        "Src"=>"gallery.Src",
        "size"=>"gallery.size",
        "Loaded"=>"gallery.Loaded",
        "Description"=>"gallery.Description",
        "Name"=>"gallery.Name",
        "Active"=>"gallery.Active",
        "test5"=>"gallery.test5",
    );
    public $PhotoID = "";
    public $MaterialID = "";
    public $materialFieldId = "";
    public $priority = "";
    public $Path = "";
    public $Src = "";
    public $size = "";
    public $Loaded = "";
    public $Description = "";
    public $Name = "";
    public $Active = "";
    public $test5 = "";
    /** Создать экземпляр класса gallery */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "gallery" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["gallery"] = array();
/**
 * Класс для работы с таблицей БД "group"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class group extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "group";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "GroupID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "GroupID"=>"GroupID",
        "Name"=>"Name",
        "Active"=>"Active",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "GroupID"=>"GroupID",
        "Name"=>"Name",
        "Active"=>"Active",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`group`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "group.GroupID,
group.Name,
group.Active",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "GroupID"=>"int",
        "Name"=>"varchar",
        "Active"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "GroupID"=>"group.GroupID",
        "Name"=>"group.Name",
        "Active"=>"group.Active",
    );
    public $GroupID = "";
    public $Name = "";
    public $Active = "";
    /** Создать экземпляр класса group */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "group" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["group"] = array();
/**
 * Класс для работы с таблицей БД "groupright"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class groupright extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "groupright";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "GroupRightID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "GroupRightID"=>"GroupRightID",
        "GroupID"=>"GroupID",
        "RightID"=>"RightID",
        "Entity"=>"Entity",
        "Key"=>"Key",
        "Ban"=>"Ban",
        "TS"=>"TS",
        "Active"=>"Active",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "GroupRightID"=>"GroupRightID",
        "GroupID"=>"GroupID",
        "RightID"=>"RightID",
        "Entity"=>"Entity",
        "Key"=>"Key",
        "Ban"=>"Ban",
        "TS"=>"TS",
        "Active"=>"Active",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`groupright`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "groupright.GroupRightID,
groupright.GroupID,
groupright.RightID,
groupright.Entity,
groupright.Key,
groupright.Ban,
groupright.TS,
groupright.Active",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "GroupRightID"=>"int",
        "GroupID"=>"int",
        "RightID"=>"int",
        "Entity"=>"varchar",
        "Key"=>"varchar",
        "Ban"=>"int",
        "TS"=>"timestamp",
        "Active"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "GroupID",
        "RightID",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "GroupRightID"=>"groupright.GroupRightID",
        "GroupID"=>"groupright.GroupID",
        "RightID"=>"groupright.RightID",
        "Entity"=>"groupright.Entity",
        "Key"=>"groupright.Key",
        "Ban"=>"groupright.Ban",
        "TS"=>"groupright.TS",
        "Active"=>"groupright.Active",
    );
    public $GroupRightID = "";
    public $GroupID = "";
    public $RightID = "";
    public $Entity = "";
    public $Key = "";
    public $Ban = "";
    public $TS = "";
    public $Active = "";
    /** Создать экземпляр класса groupright */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "groupright" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["groupright"] = array();
/**
 * Класс для работы с таблицей БД "material"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class material extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "material";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "MaterialID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "MaterialID"=>"MaterialID",
        "parent_id"=>"parent_id",
        "priority"=>"priority",
        "Name"=>"Name",
        "Url"=>"Url",
        "Created"=>"Created",
        "Modyfied"=>"Modyfied",
        "UserID"=>"UserID",
        "Draft"=>"Draft",
        "type"=>"type",
        "Published"=>"Published",
        "Active"=>"Active",
        "system"=>"system",
        "remains"=>"remains",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "MaterialID"=>"MaterialID",
        "parent_id"=>"parent_id",
        "priority"=>"priority",
        "Name"=>"Name",
        "Url"=>"Url",
        "Created"=>"Created",
        "Modyfied"=>"Modyfied",
        "UserID"=>"UserID",
        "Draft"=>"Draft",
        "type"=>"type",
        "Published"=>"Published",
        "Active"=>"Active",
        "system"=>"system",
        "remains"=>"remains",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`material`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "material.MaterialID,
material.parent_id,
material.priority,
material.Name,
material.Url,
material.Created,
material.Modyfied,
material.UserID,
material.Draft,
material.type,
material.Published,
material.Active,
material.system,
material.remains",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "MaterialID"=>"int",
        "parent_id"=>"int",
        "priority"=>"int",
        "Name"=>"varchar",
        "Url"=>"varchar",
        "Created"=>"datetime",
        "Modyfied"=>"timestamp",
        "UserID"=>"int",
        "Draft"=>"int",
        "type"=>"int",
        "Published"=>"int",
        "Active"=>"int",
        "system"=>"int",
        "remains"=>"float",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "Url",
        "UserID",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "MaterialID"=>"material.MaterialID",
        "parent_id"=>"material.parent_id",
        "priority"=>"material.priority",
        "Name"=>"material.Name",
        "Url"=>"material.Url",
        "Created"=>"material.Created",
        "Modyfied"=>"material.Modyfied",
        "UserID"=>"material.UserID",
        "Draft"=>"material.Draft",
        "type"=>"material.type",
        "Published"=>"material.Published",
        "Active"=>"material.Active",
        "system"=>"material.system",
        "remains"=>"material.remains",
    );
    public $MaterialID = "";
    public $parent_id = "";
    public $priority = "";
    public $Name = "";
    public $Url = "";
    public $Created = "";
    public $Modyfied = "";
    public $UserID = "";
    public $Draft = "";
    public $type = "";
    public $Published = "";
    public $Active = "";
    public $system = "";
    public $remains = "";
    /** Создать экземпляр класса material */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "material" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["material"] = array();
/**
 * Класс для работы с таблицей БД "materialfield"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class materialfield extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "materialfield";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "MaterialFieldID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "MaterialFieldID"=>"MaterialFieldID",
        "FieldID"=>"FieldID",
        "MaterialID"=>"MaterialID",
        "Value"=>"Value",
        "numeric_value"=>"numeric_value",
        "locale"=>"locale",
        "Active"=>"Active",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "MaterialFieldID"=>"MaterialFieldID",
        "FieldID"=>"FieldID",
        "MaterialID"=>"MaterialID",
        "Value"=>"Value",
        "numeric_value"=>"numeric_value",
        "locale"=>"locale",
        "Active"=>"Active",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`materialfield`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "materialfield.MaterialFieldID,
materialfield.FieldID,
materialfield.MaterialID,
materialfield.Value,
materialfield.numeric_value,
materialfield.locale,
materialfield.Active",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "MaterialFieldID"=>"int",
        "FieldID"=>"int",
        "MaterialID"=>"int",
        "Value"=>"text",
        "numeric_value"=>"double",
        "locale"=>"varchar",
        "Active"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "FieldID",
        "MaterialID",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "MaterialFieldID"=>"materialfield.MaterialFieldID",
        "FieldID"=>"materialfield.FieldID",
        "MaterialID"=>"materialfield.MaterialID",
        "Value"=>"materialfield.Value",
        "numeric_value"=>"materialfield.numeric_value",
        "locale"=>"materialfield.locale",
        "Active"=>"materialfield.Active",
    );
    public $MaterialFieldID = "";
    public $FieldID = "";
    public $MaterialID = "";
    public $Value = "";
    public $numeric_value = "";
    public $locale = "";
    public $Active = "";
    /** Создать экземпляр класса materialfield */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "materialfield" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["materialfield"] = array();
/**
 * Класс для работы с таблицей БД "related_materials"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class related_materials extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "related_materials";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "related_materials_id";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "related_materials_id"=>"related_materials_id",
        "first_material"=>"first_material",
        "first_locale"=>"first_locale",
        "second_material"=>"second_material",
        "second_locale"=>"second_locale",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "related_materials_id"=>"related_materials_id",
        "first_material"=>"first_material",
        "first_locale"=>"first_locale",
        "second_material"=>"second_material",
        "second_locale"=>"second_locale",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`related_materials`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "related_materials.related_materials_id,
related_materials.first_material,
related_materials.first_locale,
related_materials.second_material,
related_materials.second_locale",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "related_materials_id"=>"int",
        "first_material"=>"int",
        "first_locale"=>"varchar",
        "second_material"=>"int",
        "second_locale"=>"varchar",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "related_materials_id"=>"related_materials.related_materials_id",
        "first_material"=>"related_materials.first_material",
        "first_locale"=>"related_materials.first_locale",
        "second_material"=>"related_materials.second_material",
        "second_locale"=>"related_materials.second_locale",
    );
    public $related_materials_id = "";
    public $first_material = "";
    public $first_locale = "";
    public $second_material = "";
    public $second_locale = "";
    /** Создать экземпляр класса related_materials */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "related_materials" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["related_materials"] = array();
/**
 * Класс для работы с таблицей БД "right"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class right extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "right";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "RightID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "RightID"=>"RightID",
        "Name"=>"Name",
        "Active"=>"Active",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "RightID"=>"RightID",
        "Name"=>"Name",
        "Active"=>"Active",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`right`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "right.RightID,
right.Name,
right.Active",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "RightID"=>"int",
        "Name"=>"varchar",
        "Active"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "RightID"=>"right.RightID",
        "Name"=>"right.Name",
        "Active"=>"right.Active",
    );
    public $RightID = "";
    public $Name = "";
    public $Active = "";
    /** Создать экземпляр класса right */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "right" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["right"] = array();
/**
 * Класс для работы с таблицей БД "scmstable"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class scmstable extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "scmstable";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "RowID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "RowID"=>"RowID",
        "Entity"=>"Entity",
        "EntityID"=>"EntityID",
        "MaterialID"=>"MaterialID",
        "Created"=>"Created",
        "TS"=>"TS",
        "Active"=>"Active",
        "Column0"=>"Column0",
        "Column1"=>"Column1",
        "Column2"=>"Column2",
        "Column3"=>"Column3",
        "Column4"=>"Column4",
        "Column5"=>"Column5",
        "Column6"=>"Column6",
        "Column7"=>"Column7",
        "Column8"=>"Column8",
        "Column9"=>"Column9",
        "Column10"=>"Column10",
        "Column11"=>"Column11",
        "Column12"=>"Column12",
        "Column13"=>"Column13",
        "Column14"=>"Column14",
        "Column15"=>"Column15",
        "Column16"=>"Column16",
        "Column17"=>"Column17",
        "Column18"=>"Column18",
        "Column19"=>"Column19",
        "Column20"=>"Column20",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "RowID"=>"RowID",
        "Entity"=>"Entity",
        "EntityID"=>"EntityID",
        "MaterialID"=>"MaterialID",
        "Created"=>"Created",
        "TS"=>"TS",
        "Active"=>"Active",
        "Column0"=>"Column0",
        "Column1"=>"Column1",
        "Column2"=>"Column2",
        "Column3"=>"Column3",
        "Column4"=>"Column4",
        "Column5"=>"Column5",
        "Column6"=>"Column6",
        "Column7"=>"Column7",
        "Column8"=>"Column8",
        "Column9"=>"Column9",
        "Column10"=>"Column10",
        "Column11"=>"Column11",
        "Column12"=>"Column12",
        "Column13"=>"Column13",
        "Column14"=>"Column14",
        "Column15"=>"Column15",
        "Column16"=>"Column16",
        "Column17"=>"Column17",
        "Column18"=>"Column18",
        "Column19"=>"Column19",
        "Column20"=>"Column20",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`scmstable`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "scmstable.RowID,
scmstable.Entity,
scmstable.EntityID,
scmstable.MaterialID,
scmstable.Created,
scmstable.TS,
scmstable.Active,
scmstable.Column0,
scmstable.Column1,
scmstable.Column2,
scmstable.Column3,
scmstable.Column4,
scmstable.Column5,
scmstable.Column6,
scmstable.Column7,
scmstable.Column8,
scmstable.Column9,
scmstable.Column10,
scmstable.Column11,
scmstable.Column12,
scmstable.Column13,
scmstable.Column14,
scmstable.Column15,
scmstable.Column16,
scmstable.Column17,
scmstable.Column18,
scmstable.Column19,
scmstable.Column20",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "RowID"=>"int",
        "Entity"=>"varchar",
        "EntityID"=>"varchar",
        "MaterialID"=>"int",
        "Created"=>"datetime",
        "TS"=>"timestamp",
        "Active"=>"int",
        "Column0"=>"varchar",
        "Column1"=>"varchar",
        "Column2"=>"varchar",
        "Column3"=>"varchar",
        "Column4"=>"varchar",
        "Column5"=>"varchar",
        "Column6"=>"varchar",
        "Column7"=>"varchar",
        "Column8"=>"varchar",
        "Column9"=>"varchar",
        "Column10"=>"varchar",
        "Column11"=>"varchar",
        "Column12"=>"varchar",
        "Column13"=>"varchar",
        "Column14"=>"varchar",
        "Column15"=>"varchar",
        "Column16"=>"varchar",
        "Column17"=>"varchar",
        "Column18"=>"varchar",
        "Column19"=>"varchar",
        "Column20"=>"varchar",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "RowID"=>"scmstable.RowID",
        "Entity"=>"scmstable.Entity",
        "EntityID"=>"scmstable.EntityID",
        "MaterialID"=>"scmstable.MaterialID",
        "Created"=>"scmstable.Created",
        "TS"=>"scmstable.TS",
        "Active"=>"scmstable.Active",
        "Column0"=>"scmstable.Column0",
        "Column1"=>"scmstable.Column1",
        "Column2"=>"scmstable.Column2",
        "Column3"=>"scmstable.Column3",
        "Column4"=>"scmstable.Column4",
        "Column5"=>"scmstable.Column5",
        "Column6"=>"scmstable.Column6",
        "Column7"=>"scmstable.Column7",
        "Column8"=>"scmstable.Column8",
        "Column9"=>"scmstable.Column9",
        "Column10"=>"scmstable.Column10",
        "Column11"=>"scmstable.Column11",
        "Column12"=>"scmstable.Column12",
        "Column13"=>"scmstable.Column13",
        "Column14"=>"scmstable.Column14",
        "Column15"=>"scmstable.Column15",
        "Column16"=>"scmstable.Column16",
        "Column17"=>"scmstable.Column17",
        "Column18"=>"scmstable.Column18",
        "Column19"=>"scmstable.Column19",
        "Column20"=>"scmstable.Column20",
    );
    public $RowID = "";
    public $Entity = "";
    public $EntityID = "";
    public $MaterialID = "";
    public $Created = "";
    public $TS = "";
    public $Active = "";
    public $Column0 = "";
    public $Column1 = "";
    public $Column2 = "";
    public $Column3 = "";
    public $Column4 = "";
    public $Column5 = "";
    public $Column6 = "";
    public $Column7 = "";
    public $Column8 = "";
    public $Column9 = "";
    public $Column10 = "";
    public $Column11 = "";
    public $Column12 = "";
    public $Column13 = "";
    public $Column14 = "";
    public $Column15 = "";
    public $Column16 = "";
    public $Column17 = "";
    public $Column18 = "";
    public $Column19 = "";
    public $Column20 = "";
    /** Создать экземпляр класса scmstable */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "scmstable" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["scmstable"] = array();
/**
 * Класс для работы с таблицей БД "structure"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class structure extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "structure";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "StructureID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "StructureID"=>"StructureID",
        "ParentID"=>"ParentID",
        "Name"=>"Name",
        "locale"=>"locale",
        "Created"=>"Created",
        "UserID"=>"UserID",
        "Modyfied"=>"Modyfied",
        "Url"=>"Url",
        "MaterialID"=>"MaterialID",
        "PriorityNumber"=>"PriorityNumber",
        "type"=>"type",
        "Active"=>"Active",
        "system"=>"system",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "StructureID"=>"StructureID",
        "ParentID"=>"ParentID",
        "Name"=>"Name",
        "locale"=>"locale",
        "Created"=>"Created",
        "UserID"=>"UserID",
        "Modyfied"=>"Modyfied",
        "Url"=>"Url",
        "MaterialID"=>"MaterialID",
        "PriorityNumber"=>"PriorityNumber",
        "type"=>"type",
        "Active"=>"Active",
        "system"=>"system",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`structure`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "structure.StructureID,
structure.ParentID,
structure.Name,
structure.locale,
structure.Created,
structure.UserID,
structure.Modyfied,
structure.Url,
structure.MaterialID,
structure.PriorityNumber,
structure.type,
structure.Active,
structure.system",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "StructureID"=>"int",
        "ParentID"=>"int",
        "Name"=>"varchar",
        "locale"=>"varchar",
        "Created"=>"datetime",
        "UserID"=>"int",
        "Modyfied"=>"timestamp",
        "Url"=>"varchar",
        "MaterialID"=>"int",
        "PriorityNumber"=>"int",
        "type"=>"int",
        "Active"=>"int",
        "system"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "ParentID",
        "UserID",
        "Url",
        "MaterialID",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "StructureID"=>"structure.StructureID",
        "ParentID"=>"structure.ParentID",
        "Name"=>"structure.Name",
        "locale"=>"structure.locale",
        "Created"=>"structure.Created",
        "UserID"=>"structure.UserID",
        "Modyfied"=>"structure.Modyfied",
        "Url"=>"structure.Url",
        "MaterialID"=>"structure.MaterialID",
        "PriorityNumber"=>"structure.PriorityNumber",
        "type"=>"structure.type",
        "Active"=>"structure.Active",
        "system"=>"structure.system",
    );
    public $StructureID = "";
    public $ParentID = "";
    public $Name = "";
    public $locale = "";
    public $Created = "";
    public $UserID = "";
    public $Modyfied = "";
    public $Url = "";
    public $MaterialID = "";
    public $PriorityNumber = "";
    public $type = "";
    public $Active = "";
    public $system = "";
    /** Создать экземпляр класса structure */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "structure" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["structure"] = array();
/**
 * Класс для работы с таблицей БД "structure_relation"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class structure_relation extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "structure_relation";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "structure_relation_id";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "structure_relation_id"=>"structure_relation_id",
        "parent_id"=>"parent_id",
        "child_id"=>"child_id",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "structure_relation_id"=>"structure_relation_id",
        "parent_id"=>"parent_id",
        "child_id"=>"child_id",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`structure_relation`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "structure_relation.structure_relation_id,
structure_relation.parent_id,
structure_relation.child_id",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "structure_relation_id"=>"int",
        "parent_id"=>"int",
        "child_id"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "parent_id",
        "child_id",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "structure_relation_id"=>"structure_relation.structure_relation_id",
        "parent_id"=>"structure_relation.parent_id",
        "child_id"=>"structure_relation.child_id",
    );
    public $structure_relation_id = "";
    public $parent_id = "";
    public $child_id = "";
    /** Создать экземпляр класса structure_relation */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "structure_relation" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["structure_relation"] = array();
/**
 * Класс для работы с таблицей БД "structurefield"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class structurefield extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "structurefield";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "StructureFieldID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "StructureFieldID"=>"StructureFieldID",
        "StructureID"=>"StructureID",
        "FieldID"=>"FieldID",
        "Modified"=>"Modified",
        "Active"=>"Active",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "StructureFieldID"=>"StructureFieldID",
        "StructureID"=>"StructureID",
        "FieldID"=>"FieldID",
        "Modified"=>"Modified",
        "Active"=>"Active",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`structurefield`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "structurefield.StructureFieldID,
structurefield.StructureID,
structurefield.FieldID,
structurefield.Modified,
structurefield.Active",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "StructureFieldID"=>"int",
        "StructureID"=>"int",
        "FieldID"=>"int",
        "Modified"=>"timestamp",
        "Active"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "StructureID",
        "FieldID",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "StructureFieldID"=>"structurefield.StructureFieldID",
        "StructureID"=>"structurefield.StructureID",
        "FieldID"=>"structurefield.FieldID",
        "Modified"=>"structurefield.Modified",
        "Active"=>"structurefield.Active",
    );
    public $StructureFieldID = "";
    public $StructureID = "";
    public $FieldID = "";
    public $Modified = "";
    public $Active = "";
    /** Создать экземпляр класса structurefield */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "structurefield" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["structurefield"] = array();
/**
 * Класс для работы с таблицей БД "structurematerial"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class structurematerial extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "structurematerial";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "StructureMaterialID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "StructureMaterialID"=>"StructureMaterialID",
        "StructureID"=>"StructureID",
        "MaterialID"=>"MaterialID",
        "Modified"=>"Modified",
        "Active"=>"Active",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "StructureMaterialID"=>"StructureMaterialID",
        "StructureID"=>"StructureID",
        "MaterialID"=>"MaterialID",
        "Modified"=>"Modified",
        "Active"=>"Active",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`structurematerial`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "structurematerial.StructureMaterialID,
structurematerial.StructureID,
structurematerial.MaterialID,
structurematerial.Modified,
structurematerial.Active",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "StructureMaterialID"=>"int",
        "StructureID"=>"int",
        "MaterialID"=>"int",
        "Modified"=>"timestamp",
        "Active"=>"int",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "StructureID",
        "MaterialID",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "StructureMaterialID"=>"structurematerial.StructureMaterialID",
        "StructureID"=>"structurematerial.StructureID",
        "MaterialID"=>"structurematerial.MaterialID",
        "Modified"=>"structurematerial.Modified",
        "Active"=>"structurematerial.Active",
    );
    public $StructureMaterialID = "";
    public $StructureID = "";
    public $MaterialID = "";
    public $Modified = "";
    public $Active = "";
    /** Создать экземпляр класса structurematerial */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "structurematerial" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["structurematerial"] = array();
/**
 * Класс для работы с таблицей БД "unitable"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class unitable extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "unitable";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "row_id";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "row_id"=>"row_id",
        "entity_id"=>"entity_id",
        "material_id"=>"material_id",
        "entity"=>"entity",
        "active"=>"active",
        "created"=>"created",
        "ts"=>"ts",
        "Column0"=>"Column0",
        "Column1"=>"Column1",
        "Column2"=>"Column2",
        "Column3"=>"Column3",
        "Column4"=>"Column4",
        "Column5"=>"Column5",
        "Column6"=>"Column6",
        "Column7"=>"Column7",
        "Column8"=>"Column8",
        "Column9"=>"Column9",
        "Column10"=>"Column10",
        "Column11"=>"Column11",
        "Column12"=>"Column12",
        "Column13"=>"Column13",
        "Column14"=>"Column14",
        "Column15"=>"Column15",
        "Column16"=>"Column16",
        "Column17"=>"Column17",
        "Column18"=>"Column18",
        "Column19"=>"Column19",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "row_id"=>"row_id",
        "entity_id"=>"entity_id",
        "material_id"=>"material_id",
        "entity"=>"entity",
        "active"=>"active",
        "created"=>"created",
        "ts"=>"ts",
        "Column0"=>"Column0",
        "Column1"=>"Column1",
        "Column2"=>"Column2",
        "Column3"=>"Column3",
        "Column4"=>"Column4",
        "Column5"=>"Column5",
        "Column6"=>"Column6",
        "Column7"=>"Column7",
        "Column8"=>"Column8",
        "Column9"=>"Column9",
        "Column10"=>"Column10",
        "Column11"=>"Column11",
        "Column12"=>"Column12",
        "Column13"=>"Column13",
        "Column14"=>"Column14",
        "Column15"=>"Column15",
        "Column16"=>"Column16",
        "Column17"=>"Column17",
        "Column18"=>"Column18",
        "Column19"=>"Column19",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`unitable`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "unitable.row_id,
unitable.entity_id,
unitable.material_id,
unitable.entity,
unitable.active,
unitable.created,
unitable.ts,
unitable.Column0,
unitable.Column1,
unitable.Column2,
unitable.Column3,
unitable.Column4,
unitable.Column5,
unitable.Column6,
unitable.Column7,
unitable.Column8,
unitable.Column9,
unitable.Column10,
unitable.Column11,
unitable.Column12,
unitable.Column13,
unitable.Column14,
unitable.Column15,
unitable.Column16,
unitable.Column17,
unitable.Column18,
unitable.Column19",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "row_id"=>"int",
        "entity_id"=>"varchar",
        "material_id"=>"int",
        "entity"=>"varchar",
        "active"=>"int",
        "created"=>"datetime",
        "ts"=>"timestamp",
        "Column0"=>"varchar",
        "Column1"=>"varchar",
        "Column2"=>"varchar",
        "Column3"=>"varchar",
        "Column4"=>"varchar",
        "Column5"=>"varchar",
        "Column6"=>"varchar",
        "Column7"=>"varchar",
        "Column8"=>"varchar",
        "Column9"=>"varchar",
        "Column10"=>"varchar",
        "Column11"=>"varchar",
        "Column12"=>"varchar",
        "Column13"=>"varchar",
        "Column14"=>"varchar",
        "Column15"=>"varchar",
        "Column16"=>"varchar",
        "Column17"=>"varchar",
        "Column18"=>"varchar",
        "Column19"=>"varchar",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "entity_id",
        "material_id",
        "entity",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "row_id"=>"unitable.row_id",
        "entity_id"=>"unitable.entity_id",
        "material_id"=>"unitable.material_id",
        "entity"=>"unitable.entity",
        "active"=>"unitable.active",
        "created"=>"unitable.created",
        "ts"=>"unitable.ts",
        "Column0"=>"unitable.Column0",
        "Column1"=>"unitable.Column1",
        "Column2"=>"unitable.Column2",
        "Column3"=>"unitable.Column3",
        "Column4"=>"unitable.Column4",
        "Column5"=>"unitable.Column5",
        "Column6"=>"unitable.Column6",
        "Column7"=>"unitable.Column7",
        "Column8"=>"unitable.Column8",
        "Column9"=>"unitable.Column9",
        "Column10"=>"unitable.Column10",
        "Column11"=>"unitable.Column11",
        "Column12"=>"unitable.Column12",
        "Column13"=>"unitable.Column13",
        "Column14"=>"unitable.Column14",
        "Column15"=>"unitable.Column15",
        "Column16"=>"unitable.Column16",
        "Column17"=>"unitable.Column17",
        "Column18"=>"unitable.Column18",
        "Column19"=>"unitable.Column19",
    );
    public $row_id = "";
    public $entity_id = "";
    public $material_id = "";
    public $entity = "";
    public $active = "";
    public $created = "";
    public $ts = "";
    public $Column0 = "";
    public $Column1 = "";
    public $Column2 = "";
    public $Column3 = "";
    public $Column4 = "";
    public $Column5 = "";
    public $Column6 = "";
    public $Column7 = "";
    public $Column8 = "";
    public $Column9 = "";
    public $Column10 = "";
    public $Column11 = "";
    public $Column12 = "";
    public $Column13 = "";
    public $Column14 = "";
    public $Column15 = "";
    public $Column16 = "";
    public $Column17 = "";
    public $Column18 = "";
    public $Column19 = "";
    /** Создать экземпляр класса unitable */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "unitable" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["unitable"] = array();
/**
 * Класс для работы с таблицей БД "user"
 * @package SamsonActiveRecord
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @author Nikita Kotenko <kotenko@samsonos.com>
 * @version 2.0
 */
class user extends \samson\activerecord\dbRecord {
    /** Настоящее имя таблицы в БД к которой привязан данный класс */
    public static $_table_name = "user";
    /** Внутрення группировка таблицы */
    public static $_own_group = array();
    /** Название ключевого поля таблицы */
    public static $_primary = "UserID";
    /** Коллекция полей класса и имен колонок в таблице БД */
    public static $_attributes = array(
        "UserID"=>"UserID",
        "FName"=>"FName",
        "SName"=>"SName",
        "TName"=>"TName",
        "Email"=>"Email",
        "Password"=>"Password",
        "md5_email"=>"md5_email",
        "md5_password"=>"md5_password",
        "Created"=>"Created",
        "Modyfied"=>"Modyfied",
        "GroupID"=>"GroupID",
        "Active"=>"Active",
        "Online"=>"Online",
        "LastLogin"=>"LastLogin",
        "confirmed"=>"confirmed",
        "system"=>"system",
        "accessToken"=>"accessToken",
        "access_token"=>"access_token",
    );
    /** Коллекция РЕАЛЬНЫХ имен колонок в таблице БД */
    public static $_table_attributes = array(
        "UserID"=>"UserID",
        "FName"=>"FName",
        "SName"=>"SName",
        "TName"=>"TName",
        "Email"=>"Email",
        "Password"=>"Password",
        "md5_email"=>"md5_email",
        "md5_password"=>"md5_password",
        "Created"=>"Created",
        "Modyfied"=>"Modyfied",
        "GroupID"=>"GroupID",
        "Active"=>"Active",
        "Online"=>"Online",
        "LastLogin"=>"LastLogin",
        "confirmed"=>"confirmed",
        "system"=>"system",
        "accessToken"=>"accessToken",
        "access_token"=>"access_token",
    );
    /** Коллекция параметров SQL комманды для запроса к таблице */
    public static $_sql_from = array(
        "this" => "`user`",
    );
    /** Коллекция имен полей связанных таблиц для запроса к таблице БД */
    public static $_relations = array(
    );
    /** Коллекция алиасов связанных таблиц для запроса к таблице БД */
    public static $_relation_alias = array(
    );
    /** Коллекция типов связанных таблиц для запроса к таблице БД */
    public static $_relation_type = array(
    );
    /** Часть SQL-комманды на выборку полей класса для запроса к таблице БД */
    public static $_sql_select = array(
        "this" => "user.UserID,
user.FName,
user.SName,
user.TName,
user.Email,
user.Password,
user.md5_email,
user.md5_password,
user.Created,
user.Modyfied,
user.GroupID,
user.Active,
user.Online,
user.LastLogin,
user.confirmed,
user.system,
user.accessToken,
user.access_token",
    );
    /** Коллекция типов полей записи в таблице БД */
    public static $_types = array(
        "UserID"=>"int",
        "FName"=>"varchar",
        "SName"=>"varchar",
        "TName"=>"varchar",
        "Email"=>"varchar",
        "Password"=>"varchar",
        "md5_email"=>"varchar",
        "md5_password"=>"varchar",
        "Created"=>"datetime",
        "Modyfied"=>"timestamp",
        "GroupID"=>"int",
        "Active"=>"int",
        "Online"=>"int",
        "LastLogin"=>"datetime",
        "confirmed"=>"varchar",
        "system"=>"int",
        "accessToken"=>"varchar",
        "access_token"=>"varchar",
    );
    /** Коллекция имен ключей записи в таблице БД */
    public static $_indeces = array(
        "GroupID",
    );
    /** Коллекция имен уникальных полей записи в таблице БД */
    public static $_unique = array(
    );
    /** Коллекция связей между именами полей класса и именами колонок в таблице БД */
    public static $_map = array(
        "UserID"=>"user.UserID",
        "FName"=>"user.FName",
        "SName"=>"user.SName",
        "TName"=>"user.TName",
        "Email"=>"user.Email",
        "Password"=>"user.Password",
        "md5_email"=>"user.md5_email",
        "md5_password"=>"user.md5_password",
        "Created"=>"user.Created",
        "Modyfied"=>"user.Modyfied",
        "GroupID"=>"user.GroupID",
        "Active"=>"user.Active",
        "Online"=>"user.Online",
        "LastLogin"=>"user.LastLogin",
        "confirmed"=>"user.confirmed",
        "system"=>"user.system",
        "accessToken"=>"user.accessToken",
        "access_token"=>"user.access_token",
    );
    public $UserID = "";
    public $FName = "";
    public $SName = "";
    public $TName = "";
    public $Email = "";
    public $Password = "";
    public $md5_email = "";
    public $md5_password = "";
    public $Created = "";
    public $Modyfied = "";
    public $GroupID = "";
    public $Active = "";
    public $Online = "";
    public $LastLogin = "";
    public $confirmed = "";
    public $system = "";
    public $accessToken = "";
    public $access_token = "";
    /** Создать экземпляр класса user */
    public function __construct( $id = NULL, $class_name = NULL ){ if( !isset($class_name)) $class_name = "user" ; parent::__construct( $id, $class_name ); }
}
dbRecord::$instances["user"] = array();?>