<?php
namespace Search\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;

class ArticlesTable extends Table {

    public function searchConfiguration()
    {
        $manager = new Manager($this);
        return $manager
            ->value('foo')
            ->value('bar')
            ->value('baz')
            ->value('group');
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
     * Tests the filterParams method
     *
     * @return void
     */
    public function testFilterParams()
    {
        $result = $this->Articles->filterParams([
            'limit' => 10,
            'page' => 1,
            'conditions' => 'troll',
            'foo' => 'a',
            'bar' => 'b',
            'group' => 'main'
        ]);
        $this->assertEquals(['foo' => 'a', 'bar' => 'b'], $result);
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
            'bar' => 'b',
            'group' => 'main'
        ]);
        $this->assertEquals(2, $query->clause('where')->count());

        $query = $this->Articles->find('search', [
            'search' => [
                'foo' => 'a',
                'bar' => 'b',
                'group' => 'main'
            ]
        ]);
        $this->assertEquals(3, $query->clause('where')->count());
    }
}
