#Generic collections ```\samson\cms\GenericCollection```

This class is a generic approach for rendering catalogs and lists, it should be extended and overloaded to match needs of your specific project. This class is ```abstract``` and thought has only implementation of generic features and is designed to be extended and implemented to meet your tasks and build your own collection classes tree further in your project.
```php
class MyItemCollection extends \samson\cms\GenericCollection 
{
    // Your code here
}
```

##Filling collection
First of all you should implement ```fill()``` function which is abstract. It is responsible for filling your collection with data. You are fully responsible for what will be in ```$collection```, at the beginning we thought that it should contain [```\samson\activerecord\Record```](https://github.com/samsonos/php_activerecord/wiki) ancestors, but really it can be anything you want. Because only decide how to work further with this data.
The main purpuse of this function implementation is to fill ```$collection``` field:
```php
public function fill()
{
    return $this->collection = dbQuery('material')->id(array('1','2'))->exec();
}
```

##Iterating material collection
This class implements ```\Iterator``` interface for giving ability to iterate this object immediately after creation as usual array. 
> This is reasonable only if ```$collection``` is being filled in ```__construct()```, due to differnt real tasks implementation and further needed query and parameters injections, automating filling has been removed from ```__construct()```.

```php
foreach (new \samson\cms\GenericCollection() as $element) {
    ...
}
```

##Passing collection to view
This class implements [```\samson\core\IViewSettable```](https://github.com/samsonos/php_core/wiki/2.4-View) for giving ability to pass this object and it ancestors to views immediately after creation. also prefixes can be used to get access to multiple GenericCollections while rendering one single view.
```php
m()->view('product/catalog')->items(new GenericCollection())
```
And then rendered version of this ```GenericCollection``` or its ancestor class
will be available via ```items_html``` view variable.

##Generic implementation
Created hundreds of projects we have added implementation for ```GenericMaterialCollection``` and called it
```MaterialCollection```, we advice you to use it in your projects as it has all benefits that you can use.
> We have figured out that in most cases we have *blocks* which consists of *items* and we need to show them in some
way in project

This class has two additional fields:
* ```indexRenderer``` - external callable which is responsible for rendering block, rendered inner block contents is 
passed by reference.
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

So we have created two classes:
* ```ProductCollection``` - which is generic material collection implementation in our project
* ```CategoryProductCollection``` - which is specific implementation of filtered collection

```php
namespace mynamespace;

class ProductCollection extends \samson\cms\MaterialCollection
{
    /** @var string Block view file */
    protected $indexView;

    /** @var string Item view file */
    protected $itemView;

    /**
     * Block renderer
     * @param string $itemsHTML Block inner rendered content
     * @return string Rendered product block
     */
    protected function indexRenderer($itemsHTML)
    {
        return m()->view($this->indexView)->items($itemsHTML)->output();
    }

    /**
     * Product item renderer
     * @param Product $item Product for rendering
     * @return string Rendered product item
     */
    protected function itemRenderer(Product & $item)
    {
        m()->view($this->itemView);

        // Set image if it exists
        if (isset($item->gallery[0])) {
            m()->img(str_replace('cms/upload/', 'cms/upload/mini/', $item->gallery[0]));
        }

        // Set minimal quantity for html
        if($item->Остаток < 1) {
            m()->remains(1);
        }

        // Render product
        return m()
            ->class('class')
            ->cmsmaterial($item)
            ->output();
    }

    public function __construct($indexView, $itemView)
    {
        $this->indexView = $indexView;
        $this->itemView = $itemView;

        parent::__construct(array($this, 'indexRenderer'), array($this, 'itemRenderer'));
    }
}
```

```php
namespace mynamespace;

class CategoryProductCollection extends ProductCollection
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

