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

        // Cake's deprecationWarning() short-circuits when error_reporting()
        // does not include E_USER_DEPRECATED; the lowest-deps CI matrix
        // disables it. Force it on so the deprecation surfaces here.
        $previousReporting = error_reporting();
        error_reporting($previousReporting | E_USER_DEPRECATED);

        $deprecations = [];
        set_error_handler(function ($severity, $message) use (&$deprecations) {
            $deprecations[] = $message;
        }, E_USER_DEPRECATED);

        try {
            $result = $filter->process();
        } finally {
            restore_error_handler();
            error_reporting($previousReporting);
        }

        $this->assertTrue($result);
        $this->assertNotEmpty(
            $deprecations,
            'Expected a deprecation warning when the callback returns null.',
        );
        $this->assertStringContainsString(
            'Callback filter `title` returned null',
            implode("\n", $deprecations),
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
