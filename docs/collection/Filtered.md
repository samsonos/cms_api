#Filtered collection ```samsonos\cms\collection\Filtered```
This collection class is a child of [Generic collection class](Generic.md), the main purpose for this extension is to filter enitities collection using:
* *navigation filter*
* *additional field filter*

> If no filters is configured this collection acts like [Generic collection](Generic.md).

## Filtering
All filtering can be done using: 
* class field definition
* method calling

##Navigation filtering
All entities can be grouped using [Navigation](../Navigation.md) elements, every entity can be related to any amount of Navigation elements. This filtered collection navigation filtering approach should be used instead of old ```CMS::getMaterialByStructures(...)``` method, because it gives a lot more abilities and OOP opportunities.

Using this navigation elements you have to create navigation filter groups(arrays of Navigation element identifiers) which forms navigation filters, each navigation filter will be applied one after another with passing only already filtered enitity identifiers:

### Class field definition
```$navigation``` field should be declared at child class:
```php
// Collection of navigation filter groups
public $navigation = array(
  array(14,15), // First navigation filter group
  array(222,234)// Second navigation filter group
);
```

> If we have filtered our entity identifiers with first navigation group, but have not received any entity identifiers -  filtering is stopped and empty collection will be returned. 

### Method calls
We also created ```navigation()``` chainable method to add navigation filter group. For setting navigation elements you can pass: 
* Navigation identifier ```123``` or array of them ```array(123, 124, 222)```
* Navigation URL ```goods``` or array of them ```array('goods', 'market', 'discount')```

Method will automatically perform database query and create correct internal navigation filter elements for you.

##Field filtering
Any entity can have any amount of [Additional fields](../AdditionalField.md) which is brought to it by corresponding [Navigation element](../Navigation.md). This filtering approach gives scalable OOP approach for filtering entities collection with this additional fields. 

Field filtering is similair to navigation filtering and forms collection of field filter groups. Each group is array which consists of ```samson\activerecord\field``` database record object, it value for filtering and relation beetween field value and passed value(equal, greater and so on). 

### Class field definition
```$field``` field should be declared at child class:
```php
// Collection of field filter groups
$this->field = array(
  array($fieldDBRecord, 15, dbRelation::EQUAL), // First field filter group
  array($otherFieldDBRecord, '', dbRelation::NOT_EQUAL) // Second field filter group
);
```

### Method calls
We also created ```field()``` chainable method to add field filter group:
```field($idsOrUrls, $value, $relation)```
* $idsOrUrls - Single or Collection of field indentifiers of Urls
* $value - Field value
* $relation - Database request value relation

Example usage:
```php 
$this->field('price', 1000, dbRelation::GREATER)->field('photo', '', dbRelation::NOT_EQUAL);
```

## Ranged field filtering
If you want to add field range filter(from min value to max value), you can use special method ```ranged($idsOrUrls, $minValue, $maxValue)``` 

* $idsOrUrls - Single or Collection of field indentifiers
* $minValue - Range begin value
* $maxValue - Range end value

Example usage:
```php
$this->ranged('price', 500, 1500);
```

# Filling filtered collection
All filter logic is implemented in ```fill()``` method so you wont have to override it(we actually do not advice you to do so), but what if we need modify query, inject into it? For this purposes we have two special handler stacks:
* [Identifier handler stack](#identifier-handler-stack)
* [Entity handler stack](#entity-handler-stack)

> Handler stack - is an array of callbacks with additional parameters to be called at special algorithm places

For maximum database perfomance we make all filter request low-level optimized so they operate only with idetifiers and without unnecessary ```join``` and all process of filling the collection with filtered entities can be splitted in two stages:
* Step-by-step filtering with passing enitity identifiers from one step to another - *Identifier handler stack* can be used to manippulate this behaviour 
* Receiving final collection of entity instances - *Entity handler stack* can be used to manippulate this behaviour 

## Identifier handler stack
This handler stack is executed when all filtering steps([Navigation](#navigation-filtering) and [Field](#field-filtering)) is finished and we have actually formed final array of entity identifiers and are ready for retrieving their database records and all realated data. This callbacks will received ```$entityIds``` collection by reference so that they can change it:
```php
public function identifierCallback(&$entityIds, $param1, ... )
{
    if (sizeof($entityIds) >= 20) {
      $entityIds = array_slice($entityIds, 10, 20);
      return true;
    }
    
    return false;
}
```
> If any callback will return ```false``` empty collection will be returned.

To add this callback chainable ```handler(...)``` should be used see [example](#example).

## Entity handler stack
This handler stack is executed after [Identifier handler stack](#identifier-handler-stack) has finished and entity database query is created and corresponding enitity identifiers filter is set. This callback receives ```$query``` - Database query object by reference, so any needed action can be performed with it:
```php
public function entityQueryCallback(&$query, $param1, ... )
{
  $query->cond('Published', '1');
  
  return true;
}
```

> If no filters has been applied then only final entity query will be executed.

To add this callback chainable ```entityHandler(...)``` should be used see [example](#example).

# Example
```php
class myItemCollection extends \samsonos\cms\collection\Filtered
{
  // Add Navigation #15 filter
  public $navigation = array(array(15));
  
  public function entityQueryCallback(&$query, $param1, ... )
  {
    $query->cond('Published', '1');
    
    return true;
  }
  
  public function identifierCallback(&$entityIds, $param1, ... )
  {
    if (sizeof($entityIds) >= 20) {
      $entityIds = array_slice($entityIds, 10, 20);
      return true;
    }
    
    return false;
  }
  
  // Override constructor to add field filters
  public function __construct($renderer)
  {
    // Add field filters
    $this
      ->field('endDate', time(), dbRelation::GREATER_EQ)
      ->field('image', '', dbRelation::NOT_EQUAL)
      ->handler(array($this, 'identifierCallback'), array('param'))
      ->entityHandler(array($this, 'entityQueryCallback'), array('param'));;

    // Call parents
    parent::__construct($renderer);
  }
}
```

Now you get should get acquainted with next [Paged collection](Paged.md) implementation.
