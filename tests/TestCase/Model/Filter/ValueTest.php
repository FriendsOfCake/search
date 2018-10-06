<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Value;

class ValueTest extends TestCase
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
        $filter = new Value('title', $manager);
        $filter->setArgs(['title' => 'test']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title = :c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['test'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessSingleValueWithAndMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, ['mode' => 'and']);
        $filter->setArgs(['title' => 'foo']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title = :c0$/',
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
    public function testProcessSingleValueAndMultiField()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, [
            'field' => ['title', 'other'],
        ]);
        $filter->setArgs(['title' => 'foo']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title = :c0 OR Articles\.other = :c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['foo', 'foo'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessSingleValueAndMultiFieldWithAndMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, [
            'field' => ['title', 'other'],
            'mode' => 'and',
        ]);
        $filter->setArgs(['title' => 'foo']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title = :c0 AND Articles\.other = :c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['foo', 'foo'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, ['multiValue' => true]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title IN \(:c0,:c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueWithAndMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, [
            'multiValue' => true,
            'mode' => 'and',
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title IN \(:c0,:c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueAndMultiField()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, [
            'multiValue' => true,
            'field' => ['title', 'other'],
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title IN \(:c0,:c1\) ' .
            'OR Articles\.other IN \(:c2,:c3\)\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar', 'foo', 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueAndMultiFieldWithAndMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, [
            'multiValue' => true,
            'field' => ['title', 'other'],
            'mode' => 'and',
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title IN \(:c0,:c1\) ' .
            'AND Articles\.other IN \(:c2,:c3\)\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar', 'foo', 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueWithNonScalarValue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, ['multiValue' => true]);
        $filter->setArgs(['title' => ['foo' => ['bar']]]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title IN \(:c0\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [['bar']],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessEmptyMultiValue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, ['multiValue' => true]);
        $filter->setArgs(['title' => []]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertEmpty($filter->getQuery()->clause('where'));
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessDefaultFallbackForDisallowedMultiValue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, ['defaultValue' => 'default']);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title = :c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['default'],
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
        $filter = new Value('title', $manager);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertEmpty($filter->getQuery()->clause('where'));
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessCaseInsensitiveMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, [
            'multiValue' => true,
            'mode' => 'Or',
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title IN \(:c0,:c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }
}
