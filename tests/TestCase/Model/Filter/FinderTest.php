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
        $this->assertEquals(
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
        $this->assertEquals(
            ['foo'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessNonExistentFinderMethod()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Unknown finder method "nonExistent"');

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
