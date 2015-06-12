<?php
namespace Burzum\Search\Test\TestCase\Search\Search\Filter;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use Burzum\Search\Search\Manager;
use Burzum\Search\Search\Filter\Base;
use Burzum\Search\Search\Filter\Value;

class ValueTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Burzum/Search.Articles'
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
