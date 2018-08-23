<?php
namespace Search\Test\TestCase;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
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

    /**
     * @return void
     */
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

        /* @var $result \Search\Model\Filter\Base[] */
        $result = $manager->getFilters();
        $this->assertCount(7, $result);
        $this->assertInstanceOf('\Search\Model\Filter\Boolean', $result['boolean']);
        $this->assertInstanceOf('\Search\Model\Filter\Callback', $result['callback']);
        $this->assertInstanceOf('\Search\Model\Filter\Compare', $result['compare']);
        $this->assertInstanceOf('\Search\Test\TestApp\Model\Filter\TestFilter', $result['custom']);
        $this->assertInstanceOf('\Search\Model\Filter\Finder', $result['finder']);
        $this->assertInstanceOf('\Search\Model\Filter\Like', $result['like']);
        $this->assertInstanceOf('\Search\Model\Filter\Value', $result['value']);

        $this->assertEquals('bar', $result['boolean']->getConfig('foo'));
        $this->assertEquals('bar', $result['callback']->getConfig('foo'));
        $this->assertEquals('bar', $result['compare']->getConfig('foo'));
        $this->assertEquals('bar', $result['custom']->getConfig('foo'));
        $this->assertEquals('bar', $result['finder']->getConfig('foo'));
        $this->assertEquals('bar', $result['like']->getConfig('foo'));
        $this->assertEquals('bar', $result['value']->getConfig('foo'));
    }

    /**
     * @return void
     */
    public function testMagicShorthandMethods()
    {
        Configure::write('App.namespace', 'Search\Test\TestApp');

        $table = TableRegistry::get('Articles');

        $manager = new Manager($table);
        $manager->testFilter('test1');
        $manager->testFilter('test2', ['foo' => 'bar']);

        Configure::clear();

        /* @var $result \Search\Model\Filter\Base[] */
        $result = $manager->getFilters();
        $this->assertCount(2, $result);
        $this->assertInstanceOf('\Search\Test\TestApp\Model\Filter\TestFilter', $result['test1']);
        $this->assertInstanceOf('\Search\Test\TestApp\Model\Filter\TestFilter', $result['test2']);
        $this->assertEquals('bar', $result['test2']->getConfig('foo'));
    }

    /**
     * @return void
     */
    public function testAll()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);

        $this->assertEmpty($manager->getFilters());

        $manager->useCollection('other');
        $manager->add('field', 'Search.Value');
        $this->assertEmpty($manager->getFilters());

        $manager->useCollection('default');
        $manager->add('field', 'Search.Value');
        $all = $manager->getFilters();
        $this->assertCount(1, $all);
        $this->assertInstanceOf('\Search\Model\Filter\Value', $all['field']);
    }

    /**
     * @return void
     */
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
     * @return void
     */
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

        Configure::write('App.namespace', 'Search\Test\TestApp');
        $result = $manager->getFilters('my_test');
        $this->assertCount(1, $result);
        $this->assertInstanceOf('\Search\Model\Filter\Callback', $result['first']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The collection class "NonExistentCollection" does not exist
     * @return void
     */
    public function testGetFiltersNonExistentCollection()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->getFilters('non_existent');
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function testRepository()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $result = $manager->getRepository();
        $this->assertInstanceOf('\Cake\Datasource\RepositoryInterface', $result);
    }

    /**
     * @return void
     */
    public function testTable()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $result = $manager->getRepository();
        $this->assertInstanceOf('\Cake\Datasource\RepositoryInterface', $result);
    }

    /**
     * @return void
     */
    public function testCollection()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);

        $result = $manager->getCollection();
        $this->assertEquals('default', $result);

        $result = $manager->useCollection('default');
        $this->assertInstanceOf('\Search\Manager', $result);

        $manager->add('test', 'Search.Value');
        $result = $manager->useCollection('otherFilters');
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

    /**
     * @deprecated Remove with next major.
     * @return void
     */
    public function testCollectionCombined()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);

        $result = $manager->getCollection();
        $this->assertEquals('default', $result);

        $result = $manager->useCollection('default');
        $this->assertInstanceOf('\Search\Manager', $result);
    }

    /**
     * @return void
     */
    public function testInvalidCollectionClass()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table, self::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The collection must be instance of FilterCollectionInterface. Got instanceof "%s" instead',
            self::class
        ));
        $manager->getFilters();
    }
}
