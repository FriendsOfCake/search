<?php
namespace FOC\Search\Test\TestCase\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;

class SearchBehaviorTest extends TestCase {

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = array(
        'plugin.FOC/Search.Articles'
    );

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->Articles = TableRegistry::get('Articles');
        $this->Articles->addBehavior('FOC/Search.Search');
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