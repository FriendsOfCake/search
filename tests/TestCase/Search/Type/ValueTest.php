<?php
namespace FOC\Search\Test\TestCase\Search\Search\Type;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use FOC\Search\Search\Manager;
use FOC\Search\Search\Type\Base;
use FOC\Search\Search\Type\Value;

class ValueTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.FOC/Search.Articles'
    ];

    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $value = new Value('title', [], $manager);
        $value->args(['title' => ['test']]);
        $value->query($articles->find());
        $result = $value->process();
    }
}
