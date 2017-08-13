<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Finder;

class FinderTest extends TestCase
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
        $articles = TableRegistry::get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable'
        ]);
        $manager = new Manager($articles);
        $filter = new Finder('active', $manager);
        $filter->setArgs(['active' => ['foo' => 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.is_active = \:c0 AND foo = \:c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [true, 'bar'],
            Hash::extract($filter->getQuery()->valueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @expectedException \BadMethodCallException
     * @expectedExceptionMessage Unknown finder method "nonExistent"
     * @return void
     */
    public function testProcessNonExistentFinderMethod()
    {
        $articles = TableRegistry::get('FinderArticles', [
            'className' => '\Search\Test\TestApp\Model\Table\FinderArticlesTable'
        ]);
        $manager = new Manager($articles);
        $filter = new Finder('nonExistent', $manager);
        $filter->setArgs(['nonExistent' => true]);
        $filter->setQuery($articles->find());
        $filter->process();
    }
}
