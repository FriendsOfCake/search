<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\Query\SelectQuery;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Callback;

class CallbackTest extends TestCase
{
    protected array $fixtures = [
        'plugin.Search.Articles',
    ];

    /**
     * @return void
     */
    public function testProcess()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Callback('title', $manager, [
            'callback' => function (SelectQuery $query, array $args, Callback $filter) {
                $query->where(['title' => 'test']);
            },
        ]);
        $filter->setArgs(['title' => ['test']]);
        $filter->setQuery($articles->find());
        $this->assertTrue($filter->process());

        $this->assertMatchesRegularExpression(
            '/WHERE title = \:c0$/',
            $filter->getQuery()->sql(),
        );
        $this->assertSame(
            ['test'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testProcessFalse()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Callback('title', $manager, [
            'callback' => function (SelectQuery $query, array $args, Callback $filter) {
                return false;
            },
        ]);
        $filter->setArgs(['title' => ['test']]);
        $filter->setQuery($articles->find());
        $this->assertFalse($filter->process());
    }
}
