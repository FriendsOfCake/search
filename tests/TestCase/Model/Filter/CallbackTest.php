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
            'callback' => function (SelectQuery $query, array $args, Callback $filter): bool {
                $query->where(['title' => 'test']);

                return true;
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
     * A callback that forgets the `return` statement keeps the historic
     * isSearch=true semantics but raises a deprecation pointing at it. In
     * a future version this will throw instead.
     */
    public function testProcessNullReturnIsDeprecated()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Callback('title', $manager, [
            'callback' => function (SelectQuery $query, array $args, Callback $filter): void {
                // intentionally no return
            },
        ]);
        $filter->setArgs(['title' => ['test']]);
        $filter->setQuery($articles->find());

        $this->deprecated(function () use ($filter) {
            $this->assertTrue($filter->process());
        });
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
