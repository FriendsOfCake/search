<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\Base;

class Filter extends Base
{

    public function process()
    {
    }
}

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
        $filter = new Filter(
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

    public function testFieldAliasing()
    {
        $filter = new Filter(
            'field',
            $this->manager,
            []
        );

        $this->assertEquals('Articles.field', $filter->field());

        $filter->config('aliasField', false);
        $this->assertEquals('field', $filter->field());

        $filter = new Filter(
            ['field1', 'field2'],
            $this->manager,
            []
        );

        $expected = ['Articles.field1', 'Articles.field2'];
        $this->assertEquals($expected, $filter->field());
    }
}
