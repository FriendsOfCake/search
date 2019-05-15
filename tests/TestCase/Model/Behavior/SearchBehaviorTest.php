<?php

namespace Search\Test\TestCase\Model\Behavior;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\FilterCollection;

class SearchBehaviorTest extends TestCase
{
    /**
     * @var \Search\Test\TestApp\Model\Table\ArticlesTable
     */
    public $Articles;

    /**
     * @var \Search\Test\TestApp\Model\Table\CommentsTable
     */
    public $Comments;

    /**
     * @var \Search\Test\TestApp\Model\Table\GroupsTable
     */
    public $Groups;

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
            'className' => 'Search\Test\TestApp\Model\Table\ArticlesTable',
        ]);
        $this->Articles->addBehavior('Search.Search');
        $this->Comments = TableRegistry::get('Comments', [
            'className' => 'Search\Test\TestApp\Model\Table\CommentsTable',
        ]);
        $this->Comments->addBehavior('Search.Search');
        $this->Groups = TableRegistry::get('Groups', [
            'className' => 'Search\Test\TestApp\Model\Table\GroupsTable',
        ]);
        $this->Groups->addBehavior('Search.Search');
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
            ->setMethods(['_getFilters'])
            ->getMock();
        $this->Comments->behaviors()->reset();
        $this->Comments->addBehavior('Search', [
            'className' => '\\' . get_class($behavior),
        ]);

        $manager = new Manager($this->Comments);

        $params = [
            'name' => 'value',
            'date' => [
                'd' => '01',
                'm' => '01',
                'y' => '2017',
            ],
        ];
        $query = $this->Comments->find();

        $filter = $this
            ->getMockBuilder('\Search\Test\TestApp\Model\Filter\TestFilter')
            ->setConstructorArgs(['name', $manager])
            ->setMethods(['setArgs', 'skip', 'process', 'setQuery'])
            ->getMock();
        $filter
            ->expects($this->once())
            ->method('setArgs')
            ->with($params);
        $filter
            ->expects($this->once())
            ->method('setQuery')
            ->with($query);
        $filter
            ->expects($this->at(2))
            ->method('skip');
        $filter
            ->expects($this->at(2))
            ->method('process');

        $filter2 = $this
            ->getMockBuilder('\Search\Test\TestApp\Model\Filter\TestFilter')
            ->setConstructorArgs(['name', $manager, ['flatten' => false]])
            ->setMethods(['setArgs', 'skip', 'process', 'setQuery'])
            ->getMock();
        $filter2
            ->expects($this->once())
            ->method('setArgs')
            ->with($params);
        $filter2
            ->expects($this->once())
            ->method('setQuery')
            ->with($query);
        $filter2
            ->expects($this->at(2))
            ->method('skip');
        $filter2
            ->expects($this->at(2))
            ->method('process');

        $filters = new FilterCollection($manager);
        $filters['name'] = $filter;
        $filters['date'] = $filter2;

        /* @var $behavior \Search\Model\Behavior\SearchBehavior|\PHPUnit_Framework_MockObject_MockObject */
        $behavior = $this->Comments->behaviors()->get('Search');
        $behavior
            ->expects($this->once())
            ->method('_getFilters')
            ->with('default')
            ->willReturn($filters);

        $queryString = [
            'name' => 'value',
            'date' => [
                'd' => '01',
                'm' => '01',
                'y' => '2017',
            ],
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
            'group' => 'main',
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
                'page' => 1,
            ],
        ]);
        $this->assertEquals(2, $query->clause('where')->count());
        $this->assertTrue($this->Articles->isSearch());
    }

    /**
     * Test the custom "emptyValues" configuration
     *
     * @return void
     */
    public function testEmptyValues()
    {
        $queryString = [
            'foo' => 'a',
            'search' => 'b',
            'group' => false,
        ];

        $query = $this->Articles->find('search', ['search' => $queryString]);
        $this->assertSame(2, $query->clause('where')->count());

        $this->Articles->removeBehavior('Search');
        $this->Articles->addBehavior('Search.Search', [
            'emptyValues' => ['a'],
        ]);
        $this->Articles->searchManager()
            ->value('foo')
            ->like('search')
            ->value('baz')
            ->boolean('group');
        $query = $this->Articles->find('search', ['search' => $queryString]);
        $this->assertSame(2, $query->clause('where')->count());

        $this->Articles->removeBehavior('Search');
        $this->Articles->addBehavior('Search.Search', [
            'emptyValues' => ['a', false],
        ]);
        $this->Articles->searchManager()
            ->value('foo')
            ->like('search')
            ->value('baz')
            ->boolean('group');
        $query = $this->Articles->find('search', ['search' => $queryString]);
        $this->assertSame(1, $query->clause('where')->count());
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
                'group' => 'main',
            ],
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
     * @dataProvider collectionFinderProvider
     * @param string $collection The collection name.
     * @param string $queryString The query string data.
     * @param integer $expected The expected record count.
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
     * @return array
     */
    public function collectionFinderProvider()
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
     * @expectedException \Exception
     * @expectedExceptionMessage Custom finder "search" expects search arguments to be nested under key "search" in find() options.
     * @return void
     */
    public function testFindSearchException()
    {
        $this->Articles->find('search');
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
