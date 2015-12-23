<?php
namespace Search\Test\TestCase\Model\Filter;

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

    public function testSkip()
    {
        $manager = $this->getMock('\Search\Manager', null, [], 'Manager', false);
        $filter = new Filter(
            'field',
            $manager,
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
}
