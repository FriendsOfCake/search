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
        'plugin.Search.Articles'
    ];

    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager);
        $filter->args(['title' => 'test']);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title = :c0$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['test'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
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
        $filter->args(['title' => 'foo']);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title = :c0$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['foo'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
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
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title IN \(:c0,:c1\)$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
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
            'mode' => 'and'
        ]);
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title = :c0 AND Articles\.title = :c1\)$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
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
        $filter->args(['title' => ['foo' => ['bar']]]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertEmpty($filter->query()->clause('where'));
        $this->assertEmpty($filter->query()->valueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessEmptyMultiValue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, ['multiValue' => true]);
        $filter->args(['title' => []]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertEmpty($filter->query()->clause('where'));
        $this->assertEmpty($filter->query()->valueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessDefaultFallbackForDisallowedMultiValue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Value('title', $manager, ['defaultValue' => 'default']);
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title = :c0$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['default'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
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
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertEmpty($filter->query()->clause('where'));
        $this->assertEmpty($filter->query()->valueBinder()->bindings());
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
            'mode' => 'Or'
        ]);
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title IN \(:c0,:c1\)$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
        );
    }
}
