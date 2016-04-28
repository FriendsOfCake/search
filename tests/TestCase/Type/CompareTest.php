<?php
namespace Search\Test\TestCase\Type;

use Cake\Core\Configure;
use Cake\Database\Expression\BetweenExpression;
use Cake\Database\Expression\FunctionExpression;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Type\Compare;

class CompareTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles'
    ];

    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $value = new Compare('created', $manager, [
            'operator' => '>'
        ]);
        $value->args(['created' => '2012-12-12 12:12:12']);
        $value->query($articles->find());
        $value->process();

        $this->assertEquals(1, $value->query()->count());

        $value = new Compare('created', $manager, [
            'operator' => '>='
        ]);
        $value->args(['created' => '2012-12-12 12:12:12']);
        $value->query($articles->find());
        $value->process();

        $this->assertEquals(2, $value->query()->count());
    }
}
