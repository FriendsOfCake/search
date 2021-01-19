<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Model\Filter;

use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Boolean;

class BooleanTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'plugin.Search.Articles',
    ];

    /**
     * @return void
     */
    public function testProcessWithFlagOn()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => 'on']);
        $filter->setQuery($articles->find());
        $processed = $filter->process();

        $this->assertTrue($processed);
        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [true],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithFlagOff()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => 'off']);
        $filter->setQuery($articles->find());
        $processed = $filter->process();

        $this->assertTrue($processed);
        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [false],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithStringFlagTrue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => 'true']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [true],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithStringFlagFalse()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => 'false']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [false],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithBooleanFlagTrue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => true]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [true],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithBooleanFlagFalse()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => false]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [false],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithStringFlag1()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => '1']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [true],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithStringFlag0()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => '0']);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [false],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithIntegerFlag1()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => 1]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [true],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    public function testProcessWithIntegerFlag0()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => 0]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [false],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithFlagInvalid()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => 'neitherTruthyNorFalsy']);
        $filter->setQuery($articles->find());
        $processed = $filter->process();

        $this->assertFalse($processed);
        $this->assertEmpty($filter->getQuery()->clause('where'));
        $filter->getQuery()->sql();
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessMultiValueSafe()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager, ['multiValue' => true]);
        $filter->setArgs(['is_active' => [0, 1]]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertEmpty($filter->getQuery()->clause('where'));
        $filter->getQuery()->sql();
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessMultiField()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('boolean', $manager, [
            'fields' => ['is_active', 'other'],
        ]);
        $filter->setArgs(['boolean' => true]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.is_active = :c0 OR Articles\.other = :c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [true, true],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessMultiFieldWithAndMode()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('boolean', $manager, [
            'fields' => ['is_active', 'other'],
            'mode' => 'AND',
        ]);
        $filter->setArgs(['boolean' => true]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE \(Articles\.is_active = :c0 AND Articles\.other = :c1\)$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [true, true],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessDefaultFallbackForDisallowedMultiValue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager, ['defaultValue' => true]);
        $filter->setArgs(['is_active' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE Articles\.is_active = :c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [true],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessNoDefaultFallbackForDisallowedMultiValue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Boolean('is_active', $manager);
        $filter->setArgs(['is_active' => ['foo', 'bar']]);
        $filter->setQuery($articles->find());
        $filter->process();

        $this->assertEmpty($filter->getQuery()->clause('where'));
        $filter->getQuery()->sql();
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }
}
