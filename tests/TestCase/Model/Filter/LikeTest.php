<?php
declare(strict_types=1);

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
        'plugin.Search.Articles',
    ];

    /**
     * @return void
     */
    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager);
        $filter->setArgs(['title' => 'test']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title like \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            ['test'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );

        $filter->setConfig('comparison', 'ILIKE');
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title ilike \:c0$/',
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
    public function testProcessSingleValueWithAndValueMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['valueMode' => 'and']);
        $filter->setArgs(['title' => 'foo']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title like :c0$/',
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
    public function testProcessSingleValueAndMultiFieldWithAndValueMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'fields' => ['title', 'other'],
            'valueMode' => 'and',
        ]);
        $filter->setArgs(['title' => 'foo']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title like :c0 OR Articles\.other like :c1\)$/',
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
        $filter = new Like('title', $manager, ['multiValue' => true]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title like :c0 OR Articles\.title like :c1\)$/',
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
    public function testProcessMultiValueWithAndValueMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'multiValue' => true,
            'valueMode' => 'and',
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.title like :c0 AND Articles\.title like :c1\)$/',
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
        $filter = new Like('title', $manager, [
            'multiValue' => true,
            'fields' => ['title', 'other'],
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(\(Articles\.title like :c0 OR Articles\.title like :c1\) ' .
                'OR \(Articles\.other like :c2 OR Articles\.other like :c3\)\)$/',
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
    public function testProcessMultiValueAndMultiFieldWithAndFieldMode()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, [
            'multiValue' => true,
            'fields' => ['title', 'other'],
            'fieldMode' => 'and',
        ]);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(\(Articles\.title like :c0 OR Articles\.title like :c1\) ' .
                'AND \(Articles\.other like :c2 OR Articles\.other like :c3\)\)$/',
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
        $articles = TableRegistry::get('Articles');
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
        $articles = TableRegistry::get('Articles');
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
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Like('title', $manager, ['defaultValue' => 'default']);
        $filter->setArgs(['title' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.title like :c0$/',
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
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager);
        $filter->setArgs(['title' => 'part_1 ? 100% *']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['part\_1 _ 100\% %'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testWildcardsEscapingSqlserver()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, ['escaper' => 'Search.Sqlserver']);
        $filter->setArgs(['title' => 'part_1 ? 100% *']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['part[_]1 _ 100[%] %'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testWildcardsBeforeAfterSqlserver()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, ['before' => true, 'after' => true, 'escaper' => 'Search.Sqlserver']);
        $filter->setArgs(['title' => '22% 44_']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['%22[%] 44[_]%'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
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
        $filter->setArgs(['title' => '22% 44_']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['%22\% 44\_%'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
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
        $filter->setArgs(['title' => '22% 44_']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['%22% 44_%'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testWildcardsAlternativesSqlserver()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like(
            'title',
            $manager,
            ['before' => true, 'after' => true, 'wildcardAny' => '%', 'wildcardOne' => '_', 'escaper' => 'Search.Sqlserver']
        );
        $filter->setArgs(['title' => '22% 44_']);
        $filter->setQuery($articles->find());
        $filter->process();

        $filter->getQuery()->sql();
        $this->assertEquals(
            ['%22% 44_%'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }
}
