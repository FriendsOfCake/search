<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Test\TestApp\Model\Filter\TestFilter;

class BaseTest extends TestCase
{

    public $fixtures = [
        'plugin.Search.Articles'
    ];

    public function setup()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $this->manager = new Manager($table);
    }

    public function testSkip()
    {
        $filter = new TestFilter(
            'field',
            $this->manager,
            ['alwaysRun' => true, 'filterEmpty' => true]
        );

        $filter->args(['field' => '1']);
        $this->assertFalse($filter->skip());

        $filter->args(['field' => '0']);
        $this->assertFalse($filter->skip());

        $filter->args(['field' => '']);
        $this->assertTrue($filter->skip());

        $filter->args(['field' => []]);
        $this->assertTrue($filter->skip());
    }

    /**
     * @return void
     */
    public function testValue()
    {
        $filter = new TestFilter(
            'field',
            $this->manager,
            ['defaultValue' => 'default']
        );

        $filter->args(['field' => 'value']);
        $this->assertEquals('value', $filter->value());

        $filter->args(['other_field' => 'value']);
        $this->assertEquals('default', $filter->value());

        $filter->args(['field' => ['value1', 'value2']]);
        $this->assertEquals('default', $filter->value());

        $filter->config('multiValue', true);
        $filter->args(['field' => ['value1', 'value2']]);
        $this->assertEquals(['value1', 'value2'], $filter->value());
    }

    public function testFieldAliasing()
    {
        $filter = new TestFilter(
            'field',
            $this->manager,
            []
        );

        $this->assertEquals('Articles.field', $filter->field());

        $filter->config('aliasField', false);
        $this->assertEquals('field', $filter->field());

        $filter = new TestFilter(
            ['field1', 'field2'],
            $this->manager,
            []
        );

        $expected = ['Articles.field1', 'Articles.field2'];
        $this->assertEquals($expected, $filter->field());
    }
}
