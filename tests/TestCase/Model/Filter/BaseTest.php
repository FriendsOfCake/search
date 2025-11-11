<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Model\Filter;

use Cake\Datasource\RepositoryInterface;
use Cake\TestSuite\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Search\Manager;
use Search\Test\TestApp\Model\Filter\TestFilter;

class BaseTest extends TestCase
{
    protected Manager $Manager;

    protected array $fixtures = [
        'plugin.Search.Articles',
    ];

    /**
     * setup
     *
     * @return void
     */
    public function setUp(): void
    {
        $table = $this->getTableLocator()->get('Articles');
        $this->Manager = new Manager($table);
    }

    /**
     * @return array
     */
    public static function emptyDataProvider()
    {
        return [
            [''],
            [null],
            [[]],
            [['']],
        ];
    }

    /**
     * @param mixed $emptyValue Empty value.
     * @return void
     */
    #[DataProvider('emptyDataProvider')]
    public function testConstructEmptyFieldOption($emptyValue)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `field` option is invalid. Expected a non-empty string or array.');

        new TestFilter(
            'name',
            $this->Manager,
            ['fields' => $emptyValue],
        );
    }

    /**
     * @return array
     */
    public static function nonEmptyFieldDataProvider()
    {
        return [
            ['0'], ['value'], [['value']],
        ];
    }

    /**
     * @param mixed $nonEmptyValue Non empty value.
     * @return void
     */
    #[DataProvider('nonEmptyFieldDataProvider')]
    public function testConstructNonEmptyFieldOption($nonEmptyValue)
    {
        $filter = new TestFilter(
            'name',
            $this->Manager,
            ['fields' => $nonEmptyValue, 'aliasField' => false],
        );
        $this->assertEquals((array)$nonEmptyValue, $filter->fields());
    }

    /**
     * @return array
     */
    public static function nonEmptyNameDataProvider()
    {
        return [
            ['0'], ['value'],
        ];
    }

    /**
     * @param mixed $nonEmptyValue Non empty value.
     * @return void
     */
    #[DataProvider('nonEmptyNameDataProvider')]
    public function testConstructNonEmptyNameArgument($nonEmptyValue)
    {
        $filter = new TestFilter(
            $nonEmptyValue,
            $this->Manager,
            ['fields' => 'fields'],
        );
        $this->assertSame($filter->name(), $nonEmptyValue);
    }

    /**
     * @return void
     */
    public function testSkip()
    {
        $filter = new TestFilter(
            'fields',
            $this->Manager,
            ['alwaysRun' => true, 'filterEmpty' => true],
        );

        $filter->setArgs(['fields' => '1']);
        $this->assertFalse($filter->skip());

        $filter->setArgs(['fields' => '0']);
        $this->assertFalse($filter->skip());

        $filter->setArgs(['fields' => '']);
        $this->assertTrue($filter->skip());

        $filter->setArgs(['fields' => []]);
        $this->assertTrue($filter->skip());
    }

    /**
     * @return void
     */
    public function testValue()
    {
        $filter = new TestFilter(
            'fields',
            $this->Manager,
            ['defaultValue' => 'default'],
        );

        $filter->setArgs(['fields' => 'value']);
        $this->assertSame('value', $filter->value());

        $filter->setArgs(['other_field' => 'value']);
        $this->assertSame('default', $filter->value());

        $filter->setArgs(['fields' => ['value1', 'value2']]);
        $this->assertSame('default', $filter->value());
    }

    /**
     * @return void
     */
    public function testValueMultiValue()
    {
        $filter = new TestFilter(
            'fields',
            $this->Manager,
            ['defaultValue' => 'default'],
        );

        $filter->setConfig('multiValue', true);
        $filter->setArgs(['fields' => ['value1', 'value2']]);
        $this->assertEquals(['value1', 'value2'], $filter->value());
    }

    /**
     * @return void
     */
    public function testValueMultiValueSeparator()
    {
        $filter = new TestFilter(
            'fields',
            $this->Manager,
            ['defaultValue' => 'default'],
        );

        $filter->setConfig('multiValueSeparator', '|');

        $filter->setArgs(['fields' => 'value1|value2']);
        $this->assertEquals(['value1', 'value2'], $filter->value());
    }

    /**
     * @return void
     */
    public function testValueMultiValueSeparatorInvalid()
    {
        $filter = new TestFilter(
            'fields',
            $this->Manager,
            ['defaultValue' => 'default'],
        );

        $filter->setConfig('multiValue', true);

        $filter->setArgs(['fields' => 'value1|value2']);
        $this->assertEquals('value1|value2', $filter->value());
    }

    /**
     * @return void
     */
    public function testValueMultiValueSeparatorExactMatching()
    {
        $filter = new TestFilter(
            'fields',
            $this->Manager,
            ['defaultValue' => 'default'],
        );

        $filter->setConfig('multiValueSeparator', ' ');
        $filter->setConfig('multiValueExactMatching', true);

        $filter->setArgs(['fields' => 'value1 "value2 and 3" value4']);
        $this->assertEquals(['value1', 'value2 and 3', 'value4'], $filter->value());

        $filter->setConfig('multiValueExactMatching', '*');

        $filter->setArgs(['fields' => 'value1 *value2 and 3* value4']);
        $this->assertEquals(['value1', 'value2 and 3', 'value4'], $filter->value());

        $filter->setConfig('multiValueExactMatching', '/');

        $filter->setArgs(['fields' => 'value1 /value2 and 3/ value4']);
        $this->assertEquals(['value1', 'value2 and 3', 'value4'], $filter->value());
    }

    /**
     * @return void
     */
    public function testFieldAliasing()
    {
        $filter = new TestFilter(
            'field',
            $this->Manager,
            [],
        );

        $this->assertEquals(['Articles.field'], $filter->fields());

        $filter->setConfig('aliasField', false);
        $this->assertEquals(['field'], $filter->fields());

        $filter = new TestFilter(
            'name',
            $this->Manager,
            ['fields' => ['field1', 'field2']],
        );

        $expected = ['Articles.field1', 'Articles.field2'];
        $this->assertEquals($expected, $filter->fields());
    }

    /**
     * @return void
     */
    public function testFieldAliasingWithNonSupportingRepository()
    {
        $repo = $this->getMockBuilder(RepositoryInterface::class)
            ->getMock();

        $filter = new TestFilter(
            'fields',
            new Manager($repo),
            ['aliasField' => true],
        );

        $this->assertEquals(['fields'], $filter->fields());
    }

    /**
     * @return void
     */
    public function testBeforeProcessCallback()
    {
        $filter = new TestFilter(
            'fields',
            $this->Manager,
            ['beforeProcess' => function ($query, $params) {
                $query->where($params);
            }],
        );

        $filter->execute($this->Manager->getRepository()->find(), ['fields' => 'bar']);
        $this->assertNotEmpty($filter->getQuery()->clause('where'));
    }

    /**
     * Test that beforeProcess callback returning false prevent process() from running.
     *
     * @return void
     */
    public function testBeforeProcessReturnFalse()
    {
        $filter = $this->getMockBuilder(TestFilter::class)
            ->onlyMethods(['process'])
            ->setConstructorArgs([
                'fields',
                $this->Manager,
                [
                    'beforeProcess' => function ($query, $params) {
                        return false;
                    },
                ],
            ])
            ->getMock();

        $filter
            ->expects($this->never())
            ->method('process');

        $filter->execute($this->Manager->getRepository()->find(), ['fields' => 'bar']);
    }

    /**
     * Test that if beforeProcess returns array it's used as filter args.
     *
     * @return void
     */
    public function testBeforeProcessReturnArgsArray()
    {
        $filter = new TestFilter(
            'fields',
            $this->Manager,
            ['beforeProcess' => function ($query, $params) {
                $params['extra'] = 'value';

                return $params;
            }],
        );

        $filter->execute($this->Manager->getRepository()->find(), ['fields' => 'bar']);
        $this->assertEquals(['fields' => 'bar', 'extra' => 'value'], $filter->getArgs());
    }
}
