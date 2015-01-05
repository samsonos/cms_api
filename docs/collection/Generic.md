#Generic collections ```\samsonos\cms\collection\Generic```

This class is a generic approach for rendering catalogs and lists, it should be extended and overloaded to match needs of your specific project. This class is ```abstract``` and thought has only implementation of generic features and is designed to be extended and implemented to meet your tasks and build your own collection classes tree further in your project:
```php
class MyItemCollection extends \samsonos\cms\collection\Generic 
{
    // Your code here
}

// We extend our project MyItemCollection to use specific generic this project logic 
class MyFavouriteItemCollection extends MyItemCollection
{
    // We change only path to index view, all other logic and things are used from MyItemCollection
    public $indexView = 'favourite/index';
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
In the example above we have filled our collection with ```material``` table database records which has identifier 1 or 2.

##Rendering collection
Generic collections was designed as universal tool so the main thing that they need to be rendered is an external ```$renderer```. Which is the only needed construcor parameter for collection creation. It must be and implementation of [```\samson\core\IViewable``` interface](https://github.com/samsonos/php_core/wiki/2.4-View) and is used in all view render functions.

Usually you can pass current active module as renderer(if you use controller procedural approach):
```php 
m()->items(new MyItemCollection(m()));
```
Or use [```\samson\core\ExternalModule```](https://github.com/samsonos/php_core/wiki/2.-Modules) ancestor(if you use OOP controller approach):
```php
namespace myproject\items;

class Controller extends \samson\core\CompressableExternalModule
{
    public function __handler($url = null)
    {
        $this->view('index')->items(new MyItemCollection($this));
    }
}
```

###Passing collection to view
This class implements [```\samson\core\IViewSettable```](https://github.com/samsonos/php_core/wiki/2.4-View) so instance can be passed to view immediately after creation, this gives beautiness when you render collections and of course you can use  prefixes to get access to multiple collections while rendering one single view.
```php
m()->view('product/catalog')->items(new MyItemCollection(m()))->favourites(new MyFavouriteItemCollection(m()))
```
And then rendered version of this ```MyItemCollection``` or its ancestor class will be available via ```items_html``` and ```favourites_html``` view variables.

If you need to change standard behaviour of how and what is passed to view you should overload ```toView(...)``` method:
```php
public function toView($prefix = null, array $restricted = array())
{
    // Render show more button, if this is last page do nothing
    $showMore = $this->pager->current_page < $this->pager->total
        ? m()->view('list/showmore/index')->set('page', $this->pager->current_page + 1)->output()
        : '';

    return array($prefix.'html' => $this->render().$showMore);
}
```

### Generic rendering
After analyzing dozens of projects we have created generic view path fields and render functions for you:
* ```$indexView``` and ```renderIndex()``` - This is main block view path and renderer function
* ```$itemView``` and ```renderItem()``` - This is single item block view path and renderer function
* ```$emptyView``` and ```renderEmpty()``` - This is empty block view path and renderer function

> *IMPORTANT* All collection rendering is should be done only throught ```$this->renderer```, no ```m(...)``` or ```$this``` should be used.

If you want to change any of this view blocks render logic you should only overload that render function in your class:
```php
class MyFavouriteItemCollection extends MyItemCollection
{
    // Some data or argumenent specific to this collection implementation
    protected $something;
    
    // Overload only item rendering function because we really need it!
    public function renderItem($item)
    {
        return $this->renderer->view('favourite/item')->item($item)->something($this->something)
    }
}
```
> IMPORTANT! Before you will try to overload generic rendering functions - look at their code at first, maybe they already fit your needs.

##Iterating material collection
This class implements ```\Iterator``` interface so instance can be passed to ```foreach``` loop immediately after creation as usual array. 
> This is reasonable only if ```$collection``` has been filled in ```__construct()```, automating filling has been removed from ```__construct()```.

```php
foreach (new \samson\cms\MyItemCollection(m()) as $element) {
    ...
}
```

Now you get should get acquainted with more complex [Filtered collection](Filtered.md) implementation.
