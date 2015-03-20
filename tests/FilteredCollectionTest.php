<?php
/**
 * Created by Maxim Omelchenko <omelchenko@samsonos.com>
 * on 19.03.2015 at 17:43
 */
namespace samsonos\cms\collection;

require_once 'dbEntities.php';

class FilteredCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var Filtered */
    public $collection;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    public $database;

    public function setUp()
    {
        \samson\core\Error::$OUTPUT = false;
        $module = $this->getMockBuilder('\samson\core\ExternalModule')->getMock();
        $this->database = $this->getMockBuilder('\samson\activerecord\dbQuery')->getMock();
        $this->database->expects($this->any())->method($this->anything())->will($this->returnSelf());
        $this->collection = new Filtered($module, $this->database);
    }

    public function testHandler()
    {
        assertEquals($this->collection, $this->collection->handler(''));
    }

    public function testEntityHandler()
    {
        assertEquals($this->collection, $this->collection->entityHandler(''));
    }

    public function testNavigation()
    {
        assertEquals($this->collection, $this->collection->navigation(array()));
    }

    public function testField()
    {
        $this->database->expects($this->any())
            ->method('first')
            ->will($this->returnValue(true));
        assertEquals($this->collection, $this->collection->field(1, 'value'));
    }

    public function testSearch()
    {
        assertEquals($this->collection, $this->collection->search('value'));
    }

    public function testRanged()
    {
        assertEquals($this->collection, $this->collection->ranged(1, 1, 2));
    }

    public function testFill()
    {
        assertEquals($this->collection, $this->collection->fill());
    }
}
