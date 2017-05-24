<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\DateBetween;

class DateBetweenTest extends TestCase
{


    public $fixtures = [
        'plugin.Search.Articles'
    ];

    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $valueFrom = $valueTo = new DateBetween('created', $manager);

        $valueFrom->args([
            'created' => [
                'from' => '2012-12-12 12:00:00'
            ]
        ]);
        $valueTo->args([
            'created' => [
                'to' => '2012-12-12 12:55:00'
            ]
        ]);

        foreach ([$valueFrom, $valueTo] as $value) {
            $value->query($articles->find());
            $value->process();

            $count = $value->query()->count();
            $this->assertTrue($count === 1);
        }
    }
}
