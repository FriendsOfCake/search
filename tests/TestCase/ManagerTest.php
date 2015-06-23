<?php
namespace Search\Test\TestCase;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;

class ManagerTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles'
    ];

    public function testMethods()
    {
        $table = TableRegistry::get('Articles');
        $manager = new Manager($table);
        $manager->compare('test');
        $all = $manager->all();
        $this->assertInstanceOf('\Search\Type\Compare', $all['test']);
        $this->assertEquals(count($all), 1);

        $manager->value('test2');
        $all = $manager->all();
        $this->assertInstanceOf('\Search\Type\Value', $all['test2']);
        $this->assertEquals(count($all), 2);
    }
}
