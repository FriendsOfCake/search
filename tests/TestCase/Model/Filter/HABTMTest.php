<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Model\Filter;

use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\HABTM;

class HABTMTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.Search.Articles',
        'plugin.Search.ArticlesCategories',
        'plugin.Search.Categories',
    ];

    /**
     * @return void
     */
    public function testProcessSingleID()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $articles->addAssociations(['belongsToMany' => ['Categories']]);
        $manager = new Manager($articles);

        $filter = new HABTM('title', $manager, [
            'assoc' => 'Categories',
            'pkName' => 'id',
            'fkName' => 'category_id',
        ]);
        $filter->setArgs(['category_id' => ['1']]);
        $filter->setQuery($articles->find());
        $this->assertTrue($filter->process());

        $this->assertMatchesRegularExpression(
            '/Categories\.id in \(\:c0\)/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['1'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMultipleIDs()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $articles->addAssociations(['belongsToMany' => ['Categories']]);
        $manager = new Manager($articles);

        $filter = new HABTM('title', $manager, [
            'assoc' => 'Categories',
            'pkName' => 'id',
            'fkName' => 'category_id',
        ]);
        $filter->setArgs(['category_id' => ['1', '2']]);
        $filter->setQuery($articles->find());
        $this->assertTrue($filter->process());

        $this->assertMatchesRegularExpression(
            '/Categories\.id in \(\:c0,\:c1\)/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['1', '2'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessFalse()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $articles->addAssociations(['belongsToMany' => ['Categories']]);
        $manager = new Manager($articles);

        $filter = new HABTM('title', $manager, [
            'assoc' => 'Categories',
            'pkName' => 'id',
            'fkName' => 'category_id',
        ]);
        $filter->setArgs([]);
        $filter->setQuery($articles->find());
        $this->assertFalse($filter->process());
    }
}
