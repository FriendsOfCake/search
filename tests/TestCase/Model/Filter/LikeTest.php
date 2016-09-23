<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Like;

class LikeTest extends TestCase
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
    public function testDeprecatedModeOption()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['mode' => 'modeValue']);

        $this->assertEquals('modeValue', $filter->config('mode'));
        $this->assertEquals('modeValue', $filter->config('fieldMode'));
        $this->assertEquals('OR', $filter->config('valueMode'));
    }

    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager);
        $filter->args(['title' => 'test']);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title like \:c0$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['test'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
        );

        $filter->config('comparison', 'ILIKE');
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title ilike \:c0$/',
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
    public function testProcessSingleValueWithAndValueMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['valueMode' => 'and']);
        $filter->args(['title' => 'foo']);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title like :c0$/',
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
    public function testProcessSingleValueAndMultiFieldWithAndValueMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'field' => ['title', 'other'],
            'valueMode' => 'and'
        ]);
        $filter->args(['title' => 'foo']);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title like :c0 OR Articles\.other like :c1\)$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['foo', 'foo'],
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
        $filter = new Like('title', $manager, ['multiValue' => true]);
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title like :c0 OR Articles\.title like :c1\)$/',
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
    public function testProcessMultiValueWithAndValueMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'multiValue' => true,
            'valueMode' => 'and'
        ]);
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title like :c0 AND Articles\.title like :c1\)$/',
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
    public function testProcessMultiValueAndMultiField()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'multiValue' => true,
            'field' => ['title', 'other']
        ]);
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(\(Articles\.title like :c0 OR Articles\.title like :c1\) ' .
                'OR \(Articles\.other like :c2 OR Articles\.other like :c3\)\)$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar', 'foo', 'bar'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiValueAndMultiFieldWithAndFieldMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'multiValue' => true,
            'field' => ['title', 'other'],
            'fieldMode' => 'and'
        ]);
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(\(Articles\.title like :c0 OR Articles\.title like :c1\) ' .
                'AND \(Articles\.other like :c2 OR Articles\.other like :c3\)\)$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['foo', 'bar', 'foo', 'bar'],
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
        $filter = new Like('title', $manager, ['multiValue' => true]);
        $filter->args(['title' => ['foo' => ['bar']]]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertEmpty($filter->query()->clause('where'));
        $filter->query()->sql();
        $this->assertEmpty($filter->query()->valueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessEmptyMultiValue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['multiValue' => true]);
        $filter->args(['title' => []]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertEmpty($filter->query()->clause('where'));
        $filter->query()->sql();
        $this->assertEmpty($filter->query()->valueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessDefaultFallbackForDisallowedMultiValue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['defaultValue' => 'default']);
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title like :c0$/',
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
        $filter = new Like('title', $manager);
        $filter->args(['title' => ['foo', 'bar']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertEmpty($filter->query()->clause('where'));
        $filter->query()->sql();
        $this->assertEmpty($filter->query()->valueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testWildcardsEscaping()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager);
        $filter->args(['title' => 'part_1 ? 100% *']);
        $filter->query($articles->find());
        $filter->process();

        $filter->query()->sql();
        $this->assertEquals(
            ['part\_1 _ 100\% %'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testWildcardsBeforeAfter()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, ['before' => true, 'after' => true]);
        $filter->args(['title' => '22% 44_']);
        $filter->query($articles->find());
        $filter->process();

        $filter->query()->sql();
        $this->assertEquals(
            ['%22\% 44\_%'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testWildcardsAlternatives()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like(
            'title',
            $manager,
            ['before' => true, 'after' => true, 'wildcardAny' => '%', 'wildcardOne' => '_']
        );
        $filter->args(['title' => '22% 44_']);
        $filter->query($articles->find());
        $filter->process();

        $filter->query()->sql();
        $this->assertEquals(
            ['%22% 44_%'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
        );
    }
}
