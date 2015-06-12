<?php
namespace Burzum\Search\Test\TestCase\Search;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Burzum\Search\Search\Manager;

class ManagerTest extends TestCase {

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Burzum/Search.Articles'
    ];

    /**
     * testAdd
     *
     * @return void
     */
    public function testAdd()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->add('test', 'Compare');
        $all = $manager->getFilters();
        $this->assertInstanceOf('\Burzum\Search\Search\Filter\Compare', $all['test']);
        $this->assertEquals(count($all), 1);

        $manager->add('test2', 'Value');
        $all = $manager->getFilters();
        $this->assertInstanceOf('\Burzum\Search\Search\Filter\Value', $all['test2']);
        $this->assertEquals(count($all), 2);
    }

    /**
     * testRemove
     */
    public function testRemove()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->add('test', 'Compare');
        $all = $manager->getFilters();
        $this->assertEquals(count($all), 1);
        $manager->remove('test');
        $all = $manager->getFilters();
        $this->assertEquals(count($all), 0);
    }
}
