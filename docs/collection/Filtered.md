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

## Field filtering
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
```php 
$this->field('price', 1000, dbRelation::GREATER)->field('photo', '', dbRelation::NOT_EQUAL);
```

# Filling filtered collection
All filter logic is implemented in ```fill()``` method so you wont have to override it(we actually do not advice you to do so), but what if we need modify query, inject into it? For this purposes we have two special handler stacks:
* identifier handler stack
* entity handler stack
* 
## Identifier handler stack
For maximum database perfomance we make all filter request low-level optimized so they operate only with idetifiers and without unnecessary ```join``` and all process of filling the collection with filtered entities can be splitted in two stages:
* Step-by-step filtering with passing enitity identifiers from one step to another
* Receiving final collection of entity instances

# Example
```php
class myItemCollection extends \samsonos\cms\collection\Filtered
{
  // Add Navigation #15 filter
  public $navigation = array(array(15));
  
  // Override constructor to add field filters
  public function __construct($renderer)
  {
    // Add field filters
    $this
      ->field('endDate', time(), dbRelation::GREATER_EQ)
      ->field('image', '', dbRelation::NOT_EQUAL);

    // Call parents
    parent::__construct($renderer);
  }
}
```
