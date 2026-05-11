<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Model\Filter;

use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Mapped;

class MappedTest extends TestCase
{
    protected array $fixtures = [
        'plugin.Search.Articles',
    ];

    /**
     * Test default value is applied when no arg provided.
     *
     * @return void
     */
    public function testProcessWithDefault()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Mapped('is_active', $manager, [
            'map' => ['' => true, '0' => false, '-1' => null],
            'default' => '',
        ]);
        $filter->setArgs([]);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        // Default should not count as active search
        $this->assertFalse($result);
        $this->assertMatchesRegularExpression(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql(),
        );
        $this->assertSame(
            [true],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * Test explicit value from map is applied.
     *
     * @return void
     */
    public function testProcessWithMappedValue()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Mapped('is_active', $manager, [
            'map' => ['' => true, '0' => false, '-1' => null],
            'default' => '',
        ]);
        $filter->setArgs(['is_active' => '0']);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        // Explicit value should count as active search
        $this->assertTrue($result);
        $this->assertMatchesRegularExpression(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql(),
        );
        $this->assertSame(
            [false],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * Test null in map means no filter condition.
     *
     * @return void
     */
    public function testProcessWithNullMapping()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Mapped('is_active', $manager, [
            'map' => ['' => true, '0' => false, '-1' => null],
            'default' => '',
        ]);
        $filter->setArgs(['is_active' => '-1']);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        // Should count as active search
        $this->assertTrue($result);
        // But no WHERE clause should be added
        $this->assertEmpty($filter->getQuery()->clause('where'));
    }

    /**
     * Test unmapped non-empty values pass through directly.
     *
     * @return void
     */
    public function testProcessUnmappedValuePassthrough()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Mapped('category', $manager, [
            'map' => ['' => 'default_category', '-1' => null],
            'default' => '',
        ]);
        $filter->setArgs(['category' => 'custom_value']);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        // Passthrough value should count as active search
        $this->assertTrue($result);
        $this->assertMatchesRegularExpression(
            '/WHERE Articles\.category = \:c0$/',
            $filter->getQuery()->sql(),
        );
        $this->assertSame(
            ['custom_value'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * Test mapped values take precedence over passthrough.
     *
     * @return void
     */
    public function testProcessMappedValueTakesPrecedence()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Mapped('category', $manager, [
            'map' => ['' => 'default_category', '-1' => null, 'special' => 'mapped_special'],
            'default' => '',
        ]);
        // 'special' is in the map, so it should use the mapped value, not passthrough
        $filter->setArgs(['category' => 'special']);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        $this->assertTrue($result);
        $this->assertMatchesRegularExpression(
            '/WHERE Articles\.category = \:c0$/',
            $filter->getQuery()->sql(),
        );
        $this->assertSame(
            ['mapped_special'],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value'),
        );
    }

    /**
     * Test alwaysRun is true by default.
     *
     * @return void
     */
    public function testAlwaysRunDefault()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Mapped('is_active', $manager, [
            'map' => ['' => true],
            'default' => '',
        ]);

        $this->assertTrue($filter->getConfig('alwaysRun'));
        $this->assertFalse($filter->getConfig('filterEmpty'));
    }

    /**
     * Test with empty string explicitly provided as arg.
     *
     * @return void
     */
    public function testProcessWithEmptyStringArg()
    {
        $articles = $this->getTableLocator()->get('Articles');
        $manager = new Manager($articles);
        $filter = new Mapped('is_active', $manager, [
            'map' => ['' => true, '0' => false, '-1' => null],
            'default' => '',
        ]);
        $filter->setArgs(['is_active' => '']);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        // Empty string is the default, so not an active search
        $this->assertFalse($result);
        $this->assertMatchesRegularExpression(
            '/WHERE Articles\.is_active = \:c0$/',
            $filter->getQuery()->sql(),
        );
    }
}
