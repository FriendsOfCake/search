<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Model\Behavior;

use Cake\TestSuite\TestCase;
use Exception;
use Search\Manager;
use Search\Model\Filter\FilterCollection;
use Search\Test\TestApp\Model\Table\ArticlesTable;
use Search\Test\TestApp\Model\Table\CommentsTable;
use Search\Test\TestApp\Model\Table\SectionsTable;

class SearchBehaviorTest extends TestCase
{
    protected ArticlesTable $Articles;

    protected CommentsTable $Comments;

    protected SectionsTable $Sections;

    protected array $fixtures = [
        'plugin.Search.Articles',
        'plugin.Search.Sections',
        'core.Comments',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->getTableLocator()->clear();
        $this->Articles = $this->getTableLocator()->get('Articles', [
            'className' => 'Search\Test\TestApp\Model\Table\ArticlesTable',
        ]);
        $this->Articles->addBehavior('Search.Search');
        $this->Comments = $this->getTableLocator()->get('Comments', [
            'className' => 'Search\Test\TestApp\Model\Table\CommentsTable',
        ]);
        $this->Comments->addBehavior('Search.Search');
        $this->Sections = $this->getTableLocator()->get('Sections', [
            'className' => 'Search\Test\TestApp\Model\Table\SectionsTable',
        ]);
        $this->Sections->addBehavior('Search.Search');
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
            ->onlyMethods(['_getFilters'])
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
            'Comments.foo' => 'a',
        ];
        $query = $this->Comments->find();

        $filter = $this
            ->getMockBuilder('\Search\Test\TestApp\Model\Filter\TestFilter')
            ->setConstructorArgs(['name', $manager])
            ->onlyMethods(['setArgs', 'skip', 'process', 'setQuery'])
            ->getMock();
        $filter
            ->expects($this->once())
            ->method('setArgs')
            ->with($params)
            ->willReturnSelf();
        $filter
            ->expects($this->once())
            ->method('setQuery')
            ->with($query)
            ->willReturnSelf();
        $filter
            ->expects($this->once())
            ->method('skip');
        $filter
            ->expects($this->once())
            ->method('process');

        $filter2 = $this
            ->getMockBuilder('\Search\Test\TestApp\Model\Filter\TestFilter')
            ->setConstructorArgs(['name', $manager, ['flatten' => false]])
            ->onlyMethods(['setArgs', 'skip', 'process', 'setQuery'])
            ->getMock();
        $filter2
            ->expects($this->once())
            ->method('setArgs')
            ->with($params)
            ->willReturnSelf();
        $filter2
            ->expects($this->once())
            ->method('setQuery')
            ->with($query)
            ->willReturnSelf();
        $filter2
            ->expects($this->once())
            ->method('skip');
        $filter2
            ->expects($this->once())
            ->method('process');

        $filter3 = $this
            ->getMockBuilder('\Search\Test\TestApp\Model\Filter\TestFilter')
            ->setConstructorArgs(['name', $manager])
            ->onlyMethods(['setArgs', 'skip', 'process', 'setQuery'])
            ->getMock();
        $filter3
            ->expects($this->once())
            ->method('setArgs')
            ->with($params)
            ->willReturnSelf();
        $filter3
            ->expects($this->once())
            ->method('setQuery')
            ->with($query)
            ->willReturnSelf();
        $filter3
            ->expects($this->once())
            ->method('skip');
        $filter3
            ->expects($this->once())
            ->method('process');

        $filters = new FilterCollection($manager);
        $filters['name'] = $filter;
        $filters['date'] = $filter2;
        $filters['Comments.foo'] = $filter3;

        /** @var \Search\Model\Behavior\SearchBehavior|\PHPUnit\Framework\MockObject\MockObject $behavior */
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
            'Comments' => [
                'foo' => 'a',
            ],
        ];
        $behavior->findSearch($query, search: $queryString);
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

        $query = $this->Articles->find('search', search: $queryString);
        $this->assertEquals(3, $query->clause('where')->count());

        $queryString['search'] = '';
        $query = $this->Articles->find('search', search: $queryString);
        $this->assertEquals(2, $query->clause('where')->count());

        $queryString['foo'] = '';
        $query = $this->Articles->find('search', search: $queryString);
        $this->assertEquals(1, $query->clause('where')->count());

        $query = $this->Articles->find(
            'search',
            search: [
                'foo' => '0',
                'search' => 'b',
                'page' => '1',
            ]
        );
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
            'group' => '0',
        ];

        $query = $this->Articles->find('search', search: $queryString);
        $this->assertSame(3, $query->clause('where')->count());

        $this->Articles->removeBehavior('Search');
        $this->Articles->addBehavior('Search.Search', [
            'emptyValues' => ['a'],
        ]);
        $this->Articles->searchManager()
            ->value('foo')
            ->like('search')
            ->value('baz')
            ->boolean('group');
        $query = $this->Articles->find('search', search: $queryString);
        $this->assertSame(2, $query->clause('where')->count());

        $this->Articles->removeBehavior('Search');
        $this->Articles->addBehavior('Search.Search', [
            'emptyValues' => ['a', '0'],
        ]);
        $this->Articles->searchManager()
            ->value('foo')
            ->like('search')
            ->value('baz')
            ->boolean('group');
        $query = $this->Articles->find('search', search: $queryString);
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

        $query = $this->Comments->find('search', search: $queryString);
        $this->assertEquals(3, $query->clause('where')->count());

        $queryString['Comments']['search'] = '';
        $query = $this->Comments->find('search', search: $queryString);
        $this->assertEquals(2, $query->clause('where')->count());

        $queryString['Comments']['foo'] = '';
        $query = $this->Comments->find('search', search: $queryString);
        $this->assertEquals(1, $query->clause('where')->count());
    }

    /**
     * Test the custom "search" finder
     *
     * @dataProvider collectionFinderProvider
     * @param string $collection The collection name.
     * @param string $queryString The query string data.
     * @param int $expected The expected record count.
     * @return void
     */
    public function testCollectionFinder($collection, $queryString, $expected)
    {
        $query = $this->Sections->find('search', search: $queryString, collection: $collection);
        $this->assertEquals($expected, $query->count());
    }

    /**
     * DataProvider of testCollectionFinder
     *
     * @return array
     */
    public static function collectionFinderProvider()
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
     * @return void
     */
    public function testFindSearchException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Custom finder "search" expects search arguments to be set as "search" named argument in the find() call.');

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
