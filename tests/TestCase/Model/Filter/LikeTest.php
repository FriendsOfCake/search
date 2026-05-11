<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Model\Filter;

use Cake\Database\Connection;
use Cake\Database\Driver;
use Cake\Database\Driver\Postgres;
use Cake\Database\Driver\Sqlserver;
use Cake\ORM\Query\SelectQuery;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use ReflectionMethod;
use ReflectionProperty;
use Search\Manager;
use Search\Model\Filter\Escaper\DefaultEscaper;
use Search\Model\Filter\Escaper\EscaperInterface;
use Search\Model\Filter\Escaper\PostgresEscaper;
use Search\Model\Filter\Escaper\SqlserverEscaper;
use Search\Model\Filter\Like;

class LikeTest extends TestCase
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

        $filter = new Like('title', $manager);
        $filter->setArgs(['title' => 'test']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE Articles\.title LIKE \:c0$/',
            $filter->getQuery()->sql(),
        );
        $this->assertSame(
            ['test'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );

        $filter->setConfig('comparison', 'ILIKE');
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE Articles\.title ILIKE \:c0$/',
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
    public function testProcessSingleValueWithAndValueMode()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['valueMode' => 'and']);
        $filter->setArgs(['title' => 'foo']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE Articles\.title LIKE :c0$/',
            $filter->getQuery()->sql(),
        );
        $this->assertSame(
            ['foo'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testProcessSingleValueAndMultiFieldWithAndValueMode()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'fields' => ['title', 'other'],
            'valueMode' => 'and',
        ]);
        $filter->setArgs(['title' => 'foo']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE \(Articles\.title LIKE :c0 OR Articles\.other LIKE :c1\)$/',
            $filter->getQuery()->sql(),
        );
        $this->assertEquals(
            ['foo', 'foo'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['multiValue' => true]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE \(Articles\.title LIKE :c0 OR Articles\.title LIKE :c1\)$/',
            $filter->getQuery()->sql(),
        );
        $this->assertEquals(
            ['foo', 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueWithAndValueMode()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'multiValue' => true,
            'valueMode' => 'and',
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE \(Articles\.title LIKE :c0 AND Articles\.title LIKE :c1\)$/',
            $filter->getQuery()->sql(),
        );
        $this->assertEquals(
            ['foo', 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueAndMultiField()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'multiValue' => true,
            'fields' => ['title', 'other'],
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE \(\(Articles\.title LIKE :c0 OR Articles\.title LIKE :c1\) ' .
                'OR \(Articles\.other LIKE :c2 OR Articles\.other LIKE :c3\)\)$/',
            $filter->getQuery()->sql(),
        );
        $this->assertEquals(
            ['foo', 'bar', 'foo', 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueAndMultiFieldWithAndFieldMode()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'multiValue' => true,
            'fields' => ['title', 'other'],
            'fieldMode' => 'and',
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE \(\(Articles\.title LIKE :c0 OR Articles\.title LIKE :c1\) ' .
                'AND \(Articles\.other LIKE :c2 OR Articles\.other LIKE :c3\)\)$/',
            $filter->getQuery()->sql(),
        );
        $this->assertEquals(
            ['foo', 'bar', 'foo', 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueWithNonScalarValue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['multiValue' => true]);
        $filter->setArgs(['title' => ['foo' => ['bar']]]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertEmpty($filter->getQuery()->clause('where'));
        $filter->getQuery()->sql();
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessWithNumericFields()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('search', $manager, ['fields' => ['title', 'number'], 'colType' => ['number' => 'string']]);
        $filter->setArgs(['search' => '234']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $bindings = $filter->getQuery()->getValueBinder()->bindings();
        $expected = [
            ':c0' => [
                'value' => '234',
                'type' => 'string',
                'placeholder' => 'c0',
            ],
            ':c1' => [
                'value' => '234',
                'type' => 'string',
                'placeholder' => 'c1',
            ],
        ];
        $this->assertSame($expected, $bindings);
    }

    /**
     * @return void
     */
    public function testProcessEmptyMultiValue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['multiValue' => true]);
        $filter->setArgs(['title' => []]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertEmpty($filter->getQuery()->clause('where'));
        $filter->getQuery()->sql();
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessDefaultFallbackForDisallowedMultiValue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['defaultValue' => 'default']);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE Articles\.title LIKE :c0$/',
            $filter->getQuery()->sql(),
        );
        $this->assertEquals(
            ['default'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testProcessNoDefaultFallbackForDisallowedMultiValue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertEmpty($filter->getQuery()->clause('where'));
        $filter->getQuery()->sql();
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testWildcardsEscaping()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager);
        $filter->setArgs(['title' => 'part_1 ? 100% *']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['part\_1 _ 100\% %'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testWildcardsEscapingSqlserver()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, ['escaper' => 'Search.Sqlserver']);
        $filter->setArgs(['title' => 'part_1 ? 100% *']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['part[_]1 _ 100[%] %'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testWildcardsBeforeAfterSqlserver()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, ['before' => true, 'after' => true, 'escaper' => 'Search.Sqlserver']);
        $filter->setArgs(['title' => '22% 44_']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['%22[%] 44[_]%'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testWildcardsBeforeAfter()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, ['before' => true, 'after' => true]);
        $filter->setArgs(['title' => '22% 44_']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['%22\% 44\_%'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testWildcardsAlternatives()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Like(
            'title',
            $manager,
            ['before' => true, 'after' => true, 'wildcardAny' => '%', 'wildcardOne' => '_'],
        );
        $filter->setArgs(['title' => '22% 44_']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['%22% 44_%'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * @return void
     */
    public function testWildcardsAlternativesSqlserver()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Like(
            'title',
            $manager,
            ['before' => true, 'after' => true, 'wildcardAny' => '%', 'wildcardOne' => '_', 'escaper' => 'Search.Sqlserver'],
        );
        $filter->setArgs(['title' => '22% 44_']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['%22% 44_%'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * Driver detection must use `instanceof` against the actual driver
     * instance, not a substring match on its class name. A custom driver
     * class that extends or wraps Sqlserver under a different name must
     * still be detected as Sqlserver.
     *
     * @return void
     */
    public function testEscaperSelectionForSqlServerDriverSubclass()
    {
        $filter = $this->_filterWithDriver(new class extends Sqlserver {
            public function connect(): void
            {
            }
        });

        $this->assertInstanceOf(
            SqlserverEscaper::class,
            $this->_resolvedEscaper($filter),
        );
    }

    /**
     * Postgres drivers must pick the new Postgres escaper rather than the
     * default. This ensures that driver-specific wildcard rules can diverge
     * in future without breaking the public API.
     *
     * @return void
     */
    public function testEscaperSelectionForPostgresDriver()
    {
        $filter = $this->_filterWithDriver(new class extends Postgres {
            public function connect(): void
            {
            }
        });

        $this->assertInstanceOf(
            PostgresEscaper::class,
            $this->_resolvedEscaper($filter),
        );
    }

    /**
     * The resolved escaper must NOT stick on the filter instance: running
     * `_setEscaper()` twice against queries backed by different drivers has
     * to pick the correct escaper for each.
     *
     * @return void
     */
    public function testEscaperResolvesAfreshPerQuery()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, []);
        $filter->setArgs(['title' => 'foo']);

        // First resolution: default (sqlite) -> Default escaper.
        $this->_invokeSetEscaper($filter, $this->_queryWithDriver($articles->getConnection()->getDriver()));
        $this->assertInstanceOf(
            DefaultEscaper::class,
            $this->_resolvedEscaper($filter),
        );

        // Second resolution on same filter: Sqlserver-backed query.
        $this->_invokeSetEscaper($filter, $this->_queryWithDriver(
            new class extends Sqlserver {
                public function connect(): void
                {
                }
            },
        ));
        $this->assertInstanceOf(
            SqlserverEscaper::class,
            $this->_resolvedEscaper($filter),
        );
    }

    /**
     * Build a Like filter, point it at a stub query with the given driver,
     * and invoke `_setEscaper()` so we can inspect the resolved escaper.
     *
     * @param \Cake\Database\Driver $driver Driver instance the stub query exposes.
     * @return \Search\Model\Filter\Like
     */
    protected function _filterWithDriver(Driver $driver): Like
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, []);
        $filter->setArgs(['title' => 'foo']);
        $this->_invokeSetEscaper($filter, $this->_queryWithDriver($driver));

        return $filter;
    }

    /**
     * Invoke the protected `_setEscaper()` with the given query attached,
     * bypassing `process()` (and the schema introspection it would trigger).
     */
    protected function _invokeSetEscaper(Like $filter, SelectQuery $query): void
    {
        $filter->setQuery($query);
        (new ReflectionMethod($filter, '_setEscaper'))->invoke($filter);
    }

    /**
     * Build a `SelectQuery` stub whose `getConnection()->getDriver()` returns
     * the supplied driver instance. The query is otherwise inert — only the
     * driver-detection path of `_setEscaper()` reads from it.
     */
    protected function _queryWithDriver(Driver $driver): SelectQuery
    {
        $connection = new class ($driver) extends Connection {
            public function __construct(private Driver $injectedDriver)
            {
                parent::__construct(['driver' => 'Cake\Database\Driver\Sqlite']);
            }

            public function getDriver(string $role = self::ROLE_WRITE): Driver
            {
                return $this->injectedDriver;
            }
        };

        return new class ($connection) extends SelectQuery {
            public function __construct(private Connection $stubConnection)
            {
            }

            public function getConnection(): Connection
            {
                return $this->stubConnection;
            }
        };
    }

    /**
     * Reach into the filter's protected `_escaper` property after process().
     *
     * @param \Search\Model\Filter\Like $filter Filter to inspect.
     * @return \Search\Model\Filter\Escaper\EscaperInterface
     */
    protected function _resolvedEscaper(Like $filter): EscaperInterface
    {
        $reflection = new ReflectionProperty($filter, '_escaper');

        return $reflection->getValue($filter);
    }
}
