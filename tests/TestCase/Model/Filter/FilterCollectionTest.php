<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\Callback;
use Search\Model\Filter\FilterCollection;
use Search\Model\Filter\FilterCollectionInterface;

/**
 * Filter Collection Test
 */
class FilterCollectionTest extends TestCase
{
    public function testCollection()
    {
        $repository = TableRegistry::get('Articles');
        $manager = new Manager($repository);
        $filter = new Callback('test', $manager);

        $collection = new FilterCollection();
        $result = $collection->add($filter);
        $this->assertInstanceOf(FilterCollectionInterface::class, $result);

        $this->assertTrue($collection->has($filter));
        $this->assertTrue($collection->has('test'));
        $this->assertFalse($collection->has('doesNotExist'));

        $result = $collection->remove('test');
        $this->assertInstanceOf(FilterCollectionInterface::class, $result);
        $this->assertFalse($collection->has('test'));
    }
}
