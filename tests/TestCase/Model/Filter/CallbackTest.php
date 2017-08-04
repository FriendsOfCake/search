<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\Query;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
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

    /**
     * @return void
     */
    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Callback('title', $manager, [
            'callback' => function (Query $query, array $args, Callback $filter) {
                $query->where(['title' => 'test']);
            }
        ]);
        $filter->setArgs(['title' => ['test']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE title = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['test'],
            Hash::extract($filter->getQuery()->valueBinder()->bindings(), '{s}.value')
        );
    }
}
