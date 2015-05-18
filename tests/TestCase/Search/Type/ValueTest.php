<?php
namespace Search\Test\TestCase\Search\Search\Type;

use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\ORM\Table;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use Search\Search\Manager;
use Search\Search\Type\Value;

class ValueTest extends TestCase
{

    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $value = new Value($manager);
        $value->process();
    }
}
