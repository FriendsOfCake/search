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
            ->value('baz');
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
        $this->Articles = new ArticlesTable;
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
            'bar' => 'b'
        ]);
        $this->assertEquals(['foo' => 'a', 'bar' => 'b'], $result);
    }
}
