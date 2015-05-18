<?php
namespace Search\Test\TestCase\Model\Behavior;

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
		'plugin.Burzum\UserTools.User'
	);

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->User = TableRegistry::get('Articles');
		$this->User->addBehavior('Search.Searchable');
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Articles);
	}
}
