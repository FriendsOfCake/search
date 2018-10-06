<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\Callback;
use Search\Model\Filter\Compare;
use Search\Model\Filter\FilterCollection;
use Search\Model\Filter\FilterCollectionInterface;
use Search\Model\Filter\Value;

/**
 * Filter Collection Test
 */
class FilterCollectionTest extends TestCase
{
    public function setUp()
    {
        $repository = TableRegistry::get('Articles');
        $manager = new Manager($repository);

        $this->collection = new FilterCollection($manager);
    }

    public function testCollection()
    {
        $result = $this->collection->add('test', 'Search.Callback');
        $this->assertInstanceOf(FilterCollectionInterface::class, $result);

        $this->assertTrue($this->collection->has('test'));
        $this->assertFalse($this->collection->has('doesNotExist'));

        $result = $this->collection->remove('test');
        $this->assertInstanceOf(FilterCollectionInterface::class, $result);
        $this->assertFalse($this->collection->has('test'));

        $result = $this->collection->callback('test2');
        $this->assertInstanceOf(Callback::class, $result->get('test2'));

        $this->assertNull($this->collection->get('doesNotExist'));
    }

    /**
     * @return void
     */
    public function loadFilter()
    {
        $result = $this->collection->loadFilter('test', 'Search.Value');
        $this->assertInstanceOf(Value::class, $result);

        $this->collection->loadFilter('test', 'Search.Compare');
        $this->assertInstanceOf(Compare::class, $result);
    }

    /**
     * testLoadFilterInvalidArgumentException()
     *
     * @return void
     */
    public function testLoadFilterInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->collection->loadFilter('test', 'DOES-NOT-EXIST');
    }
}
