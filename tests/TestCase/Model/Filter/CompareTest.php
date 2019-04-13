<?php
declare(strict_types=1);
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Compare;

class CompareTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles',
    ];

    /**
     * @return void
     */
    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Compare('created', $manager, ['multiValue' => true]);
        $filter->setArgs(['created' => '2012-01-01 00:00:00']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.created >= :c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['2012-01-01 00:00:00'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Compare('time', $manager, ['field' => ['created', 'modified']]);
        $filter->setArgs(['time' => '2012-01-01 00:00:00']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.created >= :c0 AND Articles\.modified >= :c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['2012-01-01 00:00:00', '2012-01-01 00:00:00'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessModeOr()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Compare('time', $manager, ['mode' => 'OR', 'field' => ['created', 'modified']]);
        $filter->setArgs(['time' => '2012-01-01 00:00:00']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.created >= :c0 OR Articles\.modified >= :c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['2012-01-01 00:00:00', '2012-01-01 00:00:00'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueSafe()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Compare('created', $manager, ['multiValue' => true]);
        $filter->setArgs(['created' => ['foo', 'bar']]);
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
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Compare('created', $manager, ['defaultValue' => '2012-01-01 00:00:00']);
        $filter->setArgs(['created' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.created >= :c0$/',
            $filter->getQuery()->sql()
        );

        $this->assertEquals(
            ['2012-01-01 00:00:00'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessNoDefaultFallbackForDisallowedMultiValue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Compare('created', $manager);
        $filter->setArgs(['created' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertEmpty($filter->getQuery()->clause('where'));
        $filter->getQuery()->sql();
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }
}
