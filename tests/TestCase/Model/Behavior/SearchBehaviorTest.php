<?php
namespace Search\Test\TestCase\Model\Behavior;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\Base;

class ArticlesTable extends Table
{

    public function searchConfiguration()
    {
        $manager = new Manager($this);

        return $manager
            ->value('foo')
            ->like('search', ['filterEmpty' => true])
            ->value('baz')
            ->value('group', ['field' => 'Articles.group']);
    }
}

class CommentsTable extends Table
{

    public function searchConfiguration()
    {
        $manager = new Manager($this);

        return $manager
            ->value('Comments.foo')
            ->like('Comments.search', ['filterEmpty' => true, 'multiValue' => true])
            ->value('Comments.baz')
            ->value('Comments.group', ['field' => 'Comments.group'])
            ->value('group', ['multiValue' => true])
            ->value('published');
    }
}

class GroupsTable extends Table
{

    public function searchConfiguration()
    {
        $manager = new Manager($this);

        return $manager
            ->collection('frontend')
            ->value('title')
            ->collection('backend')
            ->like('title', ['before' => true, 'after' => true]);
    }
}

class Filter extends Base
{
    public function process()
    {
    }
}

class SearchBehaviorTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles',
        'core.Comments',
        'core.Groups',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        TableRegistry::clear();
        $this->Articles = TableRegistry::get('Articles', [
            'className' => 'Search\Test\TestCase\Model\Behavior\ArticlesTable'
        ]);
        $this->Articles->addBehavior('Search.Search');
        $this->Comments = TableRegistry::get('Comments', [
            'className' => 'Search\Test\TestCase\Model\Behavior\CommentsTable'
        ]);
        $this->Comments->addBehavior('Search.Search');
        $this->Groups = TableRegistry::get('Groups', [
            'className' => 'Search\Test\TestCase\Model\Behavior\GroupsTable'
        ]);
        $this->Groups->addBehavior('Search.Search');
    }

    /**
     * Tests that the query string parameters are being flattened and
     * extracted as expected before being passed to the filters for
     * processing.
     *
     * @return void
     */
    public function testFlattenAndExtractParameters()
    {
        $behavior = $this
            ->getMockBuilder('Search\Model\Behavior\SearchBehavior')
            ->setConstructorArgs([$this->Comments])
            ->setMethods(['_processFilters'])
            ->getMock();
        $this->Comments->behaviors()->reset();
        $this->Comments->addBehavior('Search', [
            'className' => '\\' . get_class($behavior)
        ]);

        $filters = $this->Comments->searchConfiguration()->all();
        $expected = [
            'Comments.foo' => 'a',
            'Comments.search' => ['b', 'c'],
            'Comments.group' => 'main',
            'group' => [
                'main',
                'secondary'
            ],
            'published' => 'Y'
        ];
        $query = $this->Comments->find();

        /* @var $behavior \Search\Model\Behavior\SearchBehavior|\PHPUnit_Framework_MockObject_MockObject */
        $behavior = $this->Comments->behaviors()->get('Search');
        $behavior
            ->expects($this->once())
            ->method('_processFilters')
            ->with($filters, $expected, $query)
            ->willReturn($query);

        $queryString = [
            'Comments' => [
                'foo' => 'a',
                'search' => ['b', 'c'],
                'group' => 'main'
            ],
            'group' => [
                '0' => 'main',
                1 => 'secondary'
            ],
            'published' => 'Y',
            'unknown' => 'foo'
        ];
         $behavior->findSearch($query, ['search' => $queryString]);
    }

    /**
     * Tests that the filters do receive the expected values, and that
     * they are being processed.
     *
     * @return void
     */
    public function testProcessFilters()
    {
        $behavior = $this
            ->getMockBuilder('Search\Model\Behavior\SearchBehavior')
            ->setConstructorArgs([$this->Comments])
            ->setMethods(['_getAllFilters'])
            ->getMock();
        $this->Comments->behaviors()->reset();
        $this->Comments->addBehavior('Search', [
            'className' => '\\' . get_class($behavior)
        ]);

        $params = [
            'name' => 'value'
        ];
        $query = $this->Comments->find();

        $filter = $this
            ->getMockBuilder('\Search\Test\TestCase\Model\Behavior\Filter')
            ->setConstructorArgs(['name', new Manager($this->Comments)])
            ->setMethods(['args', 'process', 'query'])
            ->getMock();
        $filter
            ->expects($this->at(0))
            ->method('args')
            ->with($params);
        $filter
            ->expects($this->at(1))
            ->method('query')
            ->with($query);
        $filter
            ->expects($this->at(2))
            ->method('process');

        $filters = [
            'name' => $filter
        ];

        /* @var $behavior \Search\Model\Behavior\SearchBehavior|\PHPUnit_Framework_MockObject_MockObject */
        $behavior = $this->Comments->behaviors()->get('Search');
        $behavior
            ->expects($this->once())
            ->method('_getAllFilters')
            ->with('default')
            ->willReturn($filters);

        $queryString = [
            'name' => 'value'
        ];
        $behavior->findSearch($query, ['search' => $queryString]);
    }

    /**
     * Test the custom "search" finder
     *
     * @return void
     */
    public function testFinder()
    {
        $queryString = [
            'foo' => 'a',
            'search' => 'b',
            'group' => 'main'
        ];
        $this->assertFalse($this->Articles->isSearch());

        $query = $this->Articles->find('search', ['search' => $queryString]);
        $this->assertEquals(3, $query->clause('where')->count());

        $queryString['search'] = '';
        $query = $this->Articles->find('search', ['search' => $queryString]);
        $this->assertEquals(2, $query->clause('where')->count());

        $queryString['foo'] = '';
        $query = $this->Articles->find('search', ['search' => $queryString]);
        $this->assertEquals(1, $query->clause('where')->count());

        $query = $this->Articles->find('search', [
            'search' => [
                'foo' => 0,
                'search' => 'b',
                'page' => 1
            ]
        ]);
        $this->assertEquals(2, $query->clause('where')->count());
        $this->assertTrue($this->Articles->isSearch());
    }

    /**
     * Test the custom "search" finder
     *
     * @return void
     */
    public function testAliasedFinder()
    {
        $queryString = [
            'Comments' => [
                'foo' => 'a',
                'search' => 'b',
                'group' => 'main'
            ]
        ];

        $query = $this->Comments->find('search', ['search' => $queryString]);
        $this->assertEquals(3, $query->clause('where')->count());

        $queryString['Comments']['search'] = '';
        $query = $this->Comments->find('search', ['search' => $queryString]);
        $this->assertEquals(2, $query->clause('where')->count());

        $queryString['Comments']['foo'] = '';
        $query = $this->Comments->find('search', ['search' => $queryString]);
        $this->assertEquals(1, $query->clause('where')->count());
    }

    /**
     * Test the custom "search" finder
     *
     * @dataProvider testCollectionFinderProvider
     * @return void
     */
    public function testCollectionFinder($collection, $queryString, $expected)
    {
        $query = $this->Groups->find('search', ['search' => $queryString, 'collection' => $collection]);
        $this->assertEquals($expected, $query->count());
    }

    /**
     * DataProvider of testCollectionFinder
     *
     * @return void
     */
    public function testCollectionFinderProvider()
    {
        return [
            ['frontend', ['title' => 'foo'], 1],
            ['frontend', ['title' => 'bar'], 1],
            ['frontend', ['title' => 'foobar'], 0],
            ['backend', ['title' => 'f'], 1],
            ['backend', ['title' => 'ba'], 1],
            ['backend', ['title' => 'baa'], 0],
            ['frontend', ['title' => 'fooo'], 0],
        ];
    }

    /**
     * testFindSearchException
     *
     * @expectedException Exception
     * @expectedExceptionMessage Custom finder "search" expects search arguments to be nested under key "search" in find() options.
     * @return void
     */
    public function testFindSearchException()
    {
        $query = $this->Articles->find('search');
    }

    /**
     * Tests the filterParams method
     *
     * @return void
     */
    public function testFilterParams()
    {
        $result = $this->Articles->filterParams(['foo' => 'bar']);
        $this->assertEquals(['search' => ['foo' => 'bar']], $result);
    }

    /**
     * testSearchManager
     *
     * @return void
     */
    public function testSearchManager()
    {
        $manager = $this->Articles->searchManager();
        $this->assertInstanceOf('\Search\Manager', $manager);
    }
}
