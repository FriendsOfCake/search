<?php
namespace Search\Test\TestCase;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Type\Base;

class TestType extends Base
{

    /**
     * Dummy method for testing
     */
    public function process()
    {
    }
}

class ManagerTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles'
    ];

    public function testMethods()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->compare('test');
        $all = $manager->all();
        $this->assertInstanceOf('\Search\Type\Compare', $all['test']);
        $this->assertEquals(count($all), 1);

        $manager->value('test2');
        $all = $manager->all();
        $this->assertInstanceOf('\Search\Type\Value', $all['test2']);
        $this->assertEquals(count($all), 2);
    }

    public function testLoadFilter()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $result = $manager->loadFilter('test', 'value');
        $this->assertInstanceOf('\Search\Type\Value', $result);
        $result = $manager->loadFilter('test', 'compare');
        $this->assertInstanceOf('\Search\Type\Compare', $result);
    }

    public function testAdd()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->add('testOne', 'value');
        $manager->add('testTwo', 'compare');
        $result = $manager->getFilters();
        $this->assertCount(2, $result);
        $this->assertInstanceOf('\Search\Type\Value', $result['testOne']);
        $this->assertInstanceOf('\Search\Type\Compare', $result['testTwo']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadFilterInvalidArgumentException()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->loadFilter('test', 'DOES-NOT-EXIST');
    }

    public function testGetFilters()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->add('test', 'value');
        $manager->add('test2', 'compare');
        $result = $manager->getFilters();
        $this->assertCount(2, $result);
        $this->assertInstanceOf('\Search\Type\Value', $result['test']);
        $this->assertInstanceOf('\Search\Type\Compare', $result['test2']);
    }

    public function testRemove()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->add('test', 'value');
        $manager->add('test2', 'compare');
        $result = $manager->getFilters();
        $this->assertCount(2, $result);
        $manager->remove('test2');
        $result = $manager->getFilters();
        $this->assertCount(1, $result);
        $manager->remove('test');
        $result = $manager->getFilters();
        $this->assertCount(0, $result);
    }

    public function testTable()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $result = $manager->table();
        $this->assertInstanceOf('\Cake\ORM\Table', $result);
    }

    public function testCollection()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $result = $manager->collection('default');
        $this->assertInstanceOf('\Search\Manager', $result);
        $manager->add('test', 'value');
        $result = $manager->collection('otherFilters');
        $this->assertInstanceOf('\Search\Manager', $result);
        $manager->add('test2', 'value');
        $manager->add('test3', 'value');
        $result = $manager->getFilters('default');
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('test', $result);
        $result = $manager->getFilters('otherFilters');
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('test2', $result);
        $this->assertArrayHasKey('test3', $result);
    }
}
