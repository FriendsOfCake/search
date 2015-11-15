<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\Base;
use Search\Model\Filter\Callback;

class CallbackTest extends TestCase
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

        $callback = new Callback('title', $manager, [
            'callback' => function ($query, $args, $manager) {
                $query->where(['title' => 'test']);
            }
        ]);
        $callback->args(['title' => ['test']]);
        $callback->query($articles->find());

        $query = $callback->query();
        $this->assertEmpty($query->clause('where'));

        $callback->process();
        $this->assertNotEmpty($query->clause('where'));
    }
}
