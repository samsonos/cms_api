#Filtered collection ```samsonos\cms\collection\Filtered```
This collection class is a child of [Generic collection class](Generic.md), the main purpose for this extension is to filter enitities collection using:
* *navigation filter*
* *additional field filter*
 
This filtered collection approach should be instead of old ```CMS::getMaterialByStructures(...)``` method and gives alot more abilities and OOP approaches.

> If now filters is configured this collection acts like [Generic collection](Generic.md).

## Filtering
All filtering can be done using: 
* class field definition
* method calling

##Navigation filtering
All entities can be grouped using [Navigation](../Navigation.md) elements, every entity can be related to any amount of Navigation elements. 

Using this elements you have to create navigation filter groups(arrays of Navigation element identifiers) which forms navigation filters, each navigation filter will be applied one after another with passing only already filtered enitity identifiers:

### Class field definition
```$navigation``` field should declared at child class:
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
