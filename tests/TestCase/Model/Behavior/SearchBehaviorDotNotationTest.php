<?php
namespace Search\Test\TestCase\Model\Behavior;

use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;

class CommentsTable extends Table
{

    public function searchConfiguration()
    {
        $manager = new Manager($this);
        return $manager
            ->value('Comments.foo')
            ->like('Comments.search', ['filterEmpty' => true])
            ->value('Comments.baz')
            ->value('Comments.group', ['field' => 'Comments.group']);
    }
}

class SearchBehaviorDotNotationTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Comments'
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
        $this->Comments = TableRegistry::get('Comments', [
            'className' => 'Search\Test\TestCase\Model\Behavior\CommentsTable'
        ]);
        $this->Comments->addBehavior('Search.Search');
    }

    /**
     * Test the custom "search" finder
     *
     * @return void
     */
    public function testCommentsFinder()
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
}
