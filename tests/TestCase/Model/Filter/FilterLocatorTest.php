<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\Compare;
use Search\Model\Filter\FilterLocator;
use Search\Model\Filter\Value;

/**
 * Filter Locator Test
 */
class FilterLocatorTest extends TestCase
{
    /**
     * @return void
     */
    public function testLoadFilter()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $locator = new FilterLocator($manager);

        $result = $locator->get('test', 'Search.Value');

        $this->assertInstanceOf(Value::class, $result);

        $result = $locator->get('test', 'Search.Compare');
        $this->assertInstanceOf(Compare::class, $result);
    }
}
