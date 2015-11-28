#SamsonCMS API  

[![Latest Stable Version](https://poser.pugx.org/samsonos/cms_api/v/stable.svg)](https://packagist.org/packages/samsonos/cms_api) 
[![Build Status](https://travis-ci.org/samsonos/cms_api.png)](https://travis-ci.org/samsonos/cms_api)
[![Coverage Status](https://img.shields.io/coveralls/samsonos/cms_api.svg)](https://coveralls.io/r/samsonos/cms_api?branch=master)
[![Code Climate](https://codeclimate.com/github/samsonos/cms_api/badges/gpa.svg)](https://codeclimate.com/github/samsonos/cms_api) 
[![Total Downloads](https://poser.pugx.org/samsonos/cms_api/downloads.svg)](https://packagist.org/packages/samsonos/cms_api)

> This is core classes to interact with SamsonCMS database structure

Read module documentation:
* [Material collection](https://github.com/samsonos/cms_api/blob/master/docs/MaterialCollection.md)

# Class Filter
This class has static methods to work with `filter` table. They are:
* createFilter() method allows you to create new row in `filter` table;
* resetFilters() method generates new filters from `materialfield` table. It takes only records which have filter type. Filter type can de set by CMS or directly in `field` table.

Also it has inner collection of 'filter' class objects and some methods to work with them.
* Method add() takes filter identifier as parameter and adds this filter to the collection.
* getFiltersByField() method has 1 input value: filtered field identifier. It returns array of filters related to this field.
* performFilters() method searches for suitable results, performing all filters which are in collection.
    The result is array of materials, it can be get by setting first parameter of this function.
    Other parameters are:
    * Structures which result materials should belong to;
    * External handler name. It will be called before SQL query will execute.
    * External handler parameters array, this parameters will be added to query, which is first parameter of external handler function, and filters array, which is second parameter.
That's the main info about this class!