<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Callback;

class CallbackTest extends TestCase
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
    public function testSkipProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        /* @var $filter \Search\Model\Filter\Callback|\PHPUnit_Framework_MockObject_MockObject */
        $filter = $this
            ->getMockBuilder('Search\Model\Filter\Callback')
            ->setConstructorArgs(['title', $manager])
            ->setMethods(['skip'])
            ->getMock();
        $filter
            ->expects($this->once())
            ->method('skip')
            ->willReturn(true);
        $filter->args(['title' => 'test']);
        $filter->query($articles->find());
        $filter->process();

        $this->assertEmpty($filter->query()->clause('where'));
    }

    /**
     * @return void
     */
    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Callback('title', $manager, [
            'callback' => function ($query, $args, $manager) {
                $query->where(['title' => 'test']);
            }
        ]);
        $filter->args(['title' => ['test']]);
        $filter->query($articles->find());
        $filter->process();

        $this->assertRegExp(
            '/WHERE title = \:c0$/',
            $filter->query()->sql()
        );
        $this->assertEquals(
            ['test'],
            Hash::extract($filter->query()->valueBinder()->bindings(), '{s}.value')
        );
    }
}
