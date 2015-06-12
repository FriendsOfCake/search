<?php
namespace Burzum\Search\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class SearchBehaviorTest extends TestCase {

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Burzum/Search.Articles',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Articles = TableRegistry::get('Articles');
        $this->Articles->addBehavior('Burzum/Search.Search');
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Articles);
    }

    /**
     * 
     */
    public function testFindSearch()
    {
        
    }
}
