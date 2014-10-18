#Material collections

This class is a generic approach for rendering catalogs and lists
of materials, it should be extended to match needs of specific
project.

##Iterating material collection
This class implements ```\Iterator``` interface for giving ability to iterate
this object immediately after creation as usual array. 
```php
foreach (new MaterialCollection() as $material) {
    ...
}
```

##Creating custom material collections
This class is abstract and thought has only implementation of generic features and is designed to be
extended and implemented in your specific projects. Main function is ```fill()``` which is abstract 
and should be implemented. It is responsible for filling the collection of your ```samson\cms\Material```
ancestors. 

Main purpose of this class is to give ability for quick creation of backend for showing some blocks with materials,
which must be filtered and showed with some logic dependently on specific project. For this purposes our class
has another abstract method ```render()```

##Passing material collection to view
This class implements ```\samson\core\iModuleViewable``` for giving ability
to pass this object to views immediately after creation, also prefixes can
be used to get access to multiple MaterialCollections while rendering one view.
```php
m()->view('product/catalog')->items(new MaterialCollection())
```
And then rendered version of this ```MaterialCollection``` or its ancestor class
will be available via ```items_html``` view variable.

##Generic implementation
Created hundreds of projects we have added generic implementation for ```MaterialCollection``` and called it
```GenericMaterialCollection```, we advice you to use it in your projects as it has all benefits that you can use.
> We have figured out that in most cases we have *blocks* which consists of *items* and we need to show them in some
way in project

This class has two additional fields:
* ```indexView``` - path to block view file
* ```itemRenderer``` - external callable which is responsible for rendering block item, item object is passed to it
 by reference.
 Also this class has implementation of ```render()``` method which is covering most of the cases. 

##Real world example
Example of custom MaterialCollection implementation which is creating collection of ```Product``` who is
actually ```samson\cms\Material``` ancestor with two available parameters:
* ```category``` - Which is ```samson\cms\Navigation``` identifier to filter material collection.
* ```limit``` - Maximum size of collection, in real projects you always need to show blocks with limited amount of items
* ```indexView``` - We added this parameter to make our custom material collection even more flexible, in our project
we need to show this block differently in two places.

> So, if your block *database* logic is equivalent but design for views is different, don't be shy and parametrize this by adding
> view parameter.
 
```php
namespace mynamespace;

class CategoryProductCollection extends \samson\cms\GenericMaterialCollection
{
    /** @var int Collection maximum size */
    protected $limit = 4;

    /** @var string Navigation URL  */
    protected $category;

    /**
     * Constructor
     * @param string $category Navigation URL
     * @param int $limit Collection size
     */
    public function __construct($category, $limit = 4, $indexView = 'product/sales/main')
    {
        $this->limit = $limit;

        $this->category = $category;

        // Call parent constructor
        parent::__construct($indexView, array('Product','render'));
    }

    public function dbQueryHelper(&$query)
    {
        // Perform CMSMaterial request with handlers
        $query = $query->join('gallery')
            ->group_by('Number')
            ->notnull('gallery_Src')
            ->limit($this->limit);
    }

    /**
     * Get viewed products
     * @return Product[] Collection of last viewed products
     */
    public function fill()
    {
        // Perform cms material retrieval
        if (\samson\cms\CMS::getMaterialsByStructures($this->category, $this->collection, 'Product', array($this, 'dbQueryHelper'))) {
            // Handle
        }

        return $this->collection;
    }
}
```

