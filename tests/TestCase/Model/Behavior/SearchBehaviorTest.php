<?php
namespace Search\Test\TestCase\Model\Behavior;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;

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

class SearchBehaviorTest extends TestCase
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
    }

    /**
     * [testFinder description]
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

        $query = $this->Articles->find('search', ['_search' => $queryString]);
        $this->assertEquals(3, $query->clause('where')->count());

        $queryString['search'] = '';
        $query = $this->Articles->find('search', ['_search' => $queryString]);
        $this->assertEquals(2, $query->clause('where')->count());

        $queryString['foo'] = '';
        $query = $this->Articles->find('search', ['_search' => $queryString]);
        $this->assertEquals(1, $query->clause('where')->count());
    }

    /**
     * Test the custom "search" finder
     *
     * @return void
     */
    public function testFindSearch()
    {
        $query = $this->Articles->find('search', [
            'foo' => 'a',
            'search' => 'b',
            'group' => 'main'
        ]);
        $this->assertEquals(2, $query->clause('where')->count());

        $query = $this->Articles->find('search', [
            'foo' => 0,
            'search' => 'b',
            'page' => 1
        ]);
        $this->assertEquals(2, $query->clause('where')->count());

        $query = $this->Articles->find('search', [
            '_search' => [
                'foo' => 'a',
                'search' => 'b',
                'group' => 'main'
            ]
        ]);
        $this->assertEquals(3, $query->clause('where')->count());
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
