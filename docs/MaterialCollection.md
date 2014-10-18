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
which must be filtered and showed with some logic dependently on specific project. For this purposes class
has two rendering fields(so we have *blocks* which consists of *items*):
* ```indexView``` - Path to block index view file
* ```itemView``` - Path to material item index view file

##Passing material collection to view
This class implements ```\samson\core\iModuleViewable``` for giving ability
to pass this object to views immediately after creation, also prefixes can
be used to get access to multiple MaterialCollections while rendering one view.
```php
m()->view('product/catalog')->items(new MaterialCollection())
```
And then rendered version of this ```MaterialCollection``` or its ancestor class
will be available via ```items_html``` view variable.

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

class CategoryProductCollection extends \samson\cms\MaterialCollection
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
        parent::__construct($indexView);
    }

    public function dbQueryHelper(&$query)
    {
        // Perform CMSMaterial request with handlers
        $query = $query->join('gallery')
            ->group_by( 'НомерКартинки' )
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
        if (\samson\cms\CMS::getMaterialsByStructures($this->category, $this->collection, '\purpurino\Product', array($this, 'dbQueryHelper'))) {
            // Handle
        }

        return $this->collection;
    }
}
```

