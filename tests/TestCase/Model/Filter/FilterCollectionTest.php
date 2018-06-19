<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\Callback;
use Search\Model\Filter\FilterCollection;
use Search\Model\Filter\FilterCollectionInterface;
use Search\Model\Filter\FilterLocator;

/**
 * Filter Collection Test
 */
class FilterCollectionTest extends TestCase
{
    public function testCollection()
    {
        $repository = TableRegistry::get('Articles');
        $manager = new Manager($repository);

        $collection = new FilterCollection(new FilterLocator($manager));
        $result = $collection->add('test', 'Search.Callback');
        $this->assertInstanceOf(FilterCollectionInterface::class, $result);

        $this->assertTrue($collection->has('test'));
        $this->assertFalse($collection->has('doesNotExist'));

        $result = $collection->remove('test');
        $this->assertInstanceOf(FilterCollectionInterface::class, $result);
        $this->assertFalse($collection->has('test'));
    }
}
