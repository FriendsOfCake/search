<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Model\Filter;

use BadMethodCallException;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Finder;

class FinderTest extends TestCase
{
    protected array $fixtures = [
        'plugin.Search.Articles',
    ];

    /**
     * @return void
     */
    public function testProcess()
    {
        $articles = $this->getTableLocator()->get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable',
        ]);
        $manager = new Manager($articles);
        $filter = new Finder('active', $manager);
        $filter->setArgs(['active' => ['foo' => 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE \(Articles\.is_active = \:c0 AND foo = \:c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertSame(
            [true, 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * Tests that a custom finder that requires certain keys can be used through map functionality.
     * Here we map the posted field key "form_slug" to "slug" key of the finder.
     *
     * @return void
     */
    public function testProcessMap()
    {
        $articles = $this->getTableLocator()->get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable',
        ]);
        $manager = new Manager($articles);
        $filter = new Finder('slugged', $manager, ['map' => ['slug' => 'form_slug']]);
        $filter->setArgs(['form_slug' => 'foo']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE title = :c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertSame(
            ['foo'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * Tests that a custom finder that requires certain values to be cast, usually from
     * string to int, float or bool.
     *
     * @return void
     */
    public function testProcessCast()
    {
        $articles = $this->getTableLocator()->get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable',
        ]);
        $manager = new Manager($articles);
        $filter = new Finder('user', $manager, ['cast' => ['uid' => 'int']]);
        $filter->setArgs(['uid' => '1']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE user_id = :c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertSame(
            [1],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * Tests that a custom finder that requires certain values to be cast, using
     * a custom callable.
     *
     * @return void
     */
    public function testProcessCastCallback()
    {
        $articles = $this->getTableLocator()->get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable',
        ]);
        $manager = new Manager($articles);
        $options = [
            'map' => [
                'uid' => 'user_id',
            ],
            'cast' => [
                'uid' => function ($value) {
                    return (int)$value;
                },
            ],
        ];
        $filter = new Finder('user', $manager, $options);
        $filter->setArgs(['user_id' => '1']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE user_id = :c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertSame(
            [1],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * Tests that a null input does not use casting.
     *
     * @return void
     */
    public function testProcessCastCallbackNull()
    {
        $articles = $this->getTableLocator()->get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable',
        ]);
        $manager = new Manager($articles);
        $options = [
            'cast' => [
                'slug' => function ($value) {
                    return (string)$value;
                },
            ],
        ];
        $filter = new Finder('sluggedNullable', $manager, $options);
        $filter->setArgs(['slug' => null]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertSame(
            [],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * Tests that a custom finder that requires certain values to be cast, using
     * a custom callable and null input. In this case, the callable should return
     * null if already null or empty string.
     *
     * @return void
     */
    public function testProcessCastCallbackNullableString()
    {
        $articles = $this->getTableLocator()->get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable',
        ]);
        $manager = new Manager($articles);
        $options = [
            'cast' => [
                'slug' => function ($value) {
                    if ($value === '') {
                        return null;
                    }

                    return (string)$value;
                },
            ],
        ];
        $filter = new Finder('sluggedNullable', $manager, $options);
        $filter->setArgs(['slug' => '']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertMatchesRegularExpression(
            '/WHERE \(title\) IS NULL$/',
            $filter->getQuery()->sql()
        );
        $this->assertSame(
            [],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * Tests that a custom finder that requires certain values to be cast, using
     * a custom callable and null input. In this case, the callable should return
     * null if already null or empty string.
     *
     * @return void
     */
    public function testProcessCastCallbackNullableInt()
    {
        $articles = $this->getTableLocator()->get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable',
        ]);
        $manager = new Manager($articles);
        $options = [
            'cast' => [
                'uid' => function ($value) {
                    if ($value === '') {
                        return null;
                    }

                    return (int)$value;
                },
            ],
        ];
        $filter = new Finder('userNullable', $manager, $options);
        $filter->setArgs(['uid' => '']);
        $filter->setQuery($articles->find());
        $filter->process();
        $this->assertMatchesRegularExpression(
            '/WHERE \(user_id\) IS NULL$/',
            $filter->getQuery()->sql()
        );
        $this->assertSame(
            [],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessNonExistentFinderMethod()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown finder method `nonExistent`');

        $articles = $this->getTableLocator()->get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable',
        ]);
        $manager = new Manager($articles);
        $filter = new Finder('nonExistent', $manager);
        $filter->setArgs(['nonExistent' => true]);
        $filter->setQuery($articles->find());
        $filter->process();
    }
}
