<?php
namespace Search\Test\TestCase;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;

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

    public function testShorthandMethods()
    {
        $table = TableRegistry::get('Articles');

        $options = ['foo' => 'bar'];

        $manager = new Manager($table);
        $manager->boolean('boolean', $options);
        $manager->callback('callback', $options);
        $manager->compare('compare', $options);
        $manager->custom('custom', ['className' => '\Search\Test\TestApp\Model\Filter\TestFilter'] + $options);
        $manager->finder('finder', $options);
        $manager->like('like', $options);
        $manager->value('value', $options);

        $result = $manager->getFilters();
        $this->assertCount(7, $result);
        $this->assertInstanceOf('\Search\Model\Filter\Boolean', $result['boolean']);
        $this->assertInstanceOf('\Search\Model\Filter\Callback', $result['callback']);
        $this->assertInstanceOf('\Search\Model\Filter\Compare', $result['compare']);
        $this->assertInstanceOf('\Search\Test\TestApp\Model\Filter\TestFilter', $result['custom']);
        $this->assertInstanceOf('\Search\Model\Filter\Finder', $result['finder']);
        $this->assertInstanceOf('\Search\Model\Filter\Like', $result['like']);
        $this->assertInstanceOf('\Search\Model\Filter\Value', $result['value']);

        $this->assertEquals('bar', $result['boolean']->config('foo'));
        $this->assertEquals('bar', $result['callback']->config('foo'));
        $this->assertEquals('bar', $result['compare']->config('foo'));
        $this->assertEquals('bar', $result['custom']->config('foo'));
        $this->assertEquals('bar', $result['finder']->config('foo'));
        $this->assertEquals('bar', $result['like']->config('foo'));
        $this->assertEquals('bar', $result['value']->config('foo'));
    }

    public function testMagicShorthandMethods()
    {
        Configure::write('App.namespace', 'Search\Test\TestApp');

        $table = TableRegistry::get('Articles');

        $manager = new Manager($table);
        $manager->testFilter('test1');
        $manager->testFilter('test2', ['foo' => 'bar']);

        Configure::clear();

        $result = $manager->getFilters();
        $this->assertCount(2, $result);
        $this->assertInstanceOf('\Search\Test\TestApp\Model\Filter\TestFilter', $result['test1']);
        $this->assertInstanceOf('\Search\Test\TestApp\Model\Filter\TestFilter', $result['test2']);
        $this->assertEquals('bar', $result['test2']->config('foo'));
    }

    public function testLoadFilter()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $result = $manager->loadFilter('test', 'Search.Value');
        $this->assertInstanceOf('\Search\Model\Filter\Value', $result);
        $result = $manager->loadFilter('test', 'Search.Compare');
        $this->assertInstanceOf('\Search\Model\Filter\Compare', $result);
    }

    public function testAdd()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->add('testOne', 'Search.Value');
        $manager->add('testTwo', 'Search.Compare');
        $result = $manager->getFilters();
        $this->assertCount(2, $result);
        $this->assertInstanceOf('\Search\Model\Filter\Value', $result['testOne']);
        $this->assertInstanceOf('\Search\Model\Filter\Compare', $result['testTwo']);
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
        $manager->add('test', 'Search.Value');
        $manager->add('test2', 'Search.Compare');
        $result = $manager->getFilters();
        $this->assertCount(2, $result);
        $this->assertInstanceOf('\Search\Model\Filter\Value', $result['test']);
        $this->assertInstanceOf('\Search\Model\Filter\Compare', $result['test2']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The collection "nonExistentCollection" does not exist.
     */
    public function testGetFiltersNonExistentCollection()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->getFilters('nonExistentCollection');
    }

    public function testRemove()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->add('test', 'Search.Value');
        $manager->add('test2', 'Search.Compare');
        $result = $manager->getFilters();
        $this->assertCount(2, $result);
        $manager->remove('test2');
        $result = $manager->getFilters();
        $this->assertCount(1, $result);
        $manager->remove('test');
        $result = $manager->getFilters();
        $this->assertCount(0, $result);
    }

    public function testRepository()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $result = $manager->repository();
        $this->assertInstanceOf('\Cake\Datasource\RepositoryInterface', $result);
    }

    public function testCollection()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $result = $manager->collection('default');
        $this->assertInstanceOf('\Search\Manager', $result);
        $manager->add('test', 'Search.Value');
        $result = $manager->collection('otherFilters');
        $this->assertInstanceOf('\Search\Manager', $result);
        $manager->add('test2', 'Search.Value');
        $manager->add('test3', 'Search.Value');
        $result = $manager->getFilters('default');
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('test', $result);
        $result = $manager->getFilters('otherFilters');
        $this->assertCount(2, $result);
        $this->assertArrayHasKey('test2', $result);
        $this->assertArrayHasKey('test3', $result);
    }
}
