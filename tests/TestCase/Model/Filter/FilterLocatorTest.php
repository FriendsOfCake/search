<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\FilterLocator;

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

        $result = $locator->locate('test', 'Search.Value');
        $this->assertInstanceOf('\Search\Model\Filter\Value', $result);

        $result = $locator->locate('test', 'Search.Compare');
        $this->assertInstanceOf('\Search\Model\Filter\Compare', $result);
    }
}
