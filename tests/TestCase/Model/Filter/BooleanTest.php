<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\Base;
use Search\Model\Filter\Boolean;

class BooleanTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles'
    ];

    public function testProcessWithFlagOn()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $boolean = new Boolean('is_active', $manager);
        $boolean->args(['is_active' => 'on']);
        $boolean->query($articles->find());

        $query = $boolean->query();
        $this->assertEmpty($query->clause('where'));

        $boolean->process();
        $this->assertNotEmpty($query->clause('where'));
    }

    public function testProcessWithFlagOff()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $boolean = new Boolean('is_active', $manager);
        $boolean->args(['is_active' => 'off']);
        $boolean->query($articles->find());

        $query = $boolean->query();
        $this->assertEmpty($query->clause('where'));

        $boolean->process();
        $this->assertNotEmpty($query->clause('where'));
    }

    public function testProcessWithFlagTrue()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $boolean = new Boolean('is_active', $manager);
        $boolean->args(['is_active' => 'true']);
        $boolean->query($articles->find());

        $query = $boolean->query();
        $this->assertEmpty($query->clause('where'));

        $boolean->process();
        $this->assertNotEmpty($query->clause('where'));
    }

    public function testProcessWithFlagFalse()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $boolean = new Boolean('is_active', $manager);
        $boolean->args(['is_active' => 'false']);
        $boolean->query($articles->find());

        $query = $boolean->query();
        $this->assertEmpty($query->clause('where'));

        $boolean->process();
        $this->assertNotEmpty($query->clause('where'));
    }

    public function testProcessWithFlag1()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $boolean = new Boolean('is_active', $manager);
        $boolean->args(['is_active' => '1']);
        $boolean->query($articles->find());

        $query = $boolean->query();
        $this->assertEmpty($query->clause('where'));

        $boolean->process();
        $this->assertNotEmpty($query->clause('where'));
    }

    public function testProcessWithFlag0()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $boolean = new Boolean('is_active', $manager);
        $boolean->args(['is_active' => 0]);
        $boolean->query($articles->find());

        $query = $boolean->query();
        $this->assertEmpty($query->clause('where'));

        $boolean->process();
        $this->assertNotEmpty($query->clause('where'));
    }

    public function testProcessWithFlagInvalid()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $boolean = new Boolean('is_active', $manager);
        $boolean->args(['is_active' => 'neitherTruthyNorFalsy']);
        $boolean->query($articles->find());

        $query = $boolean->query();
        $this->assertEmpty($query->clause('where'));

        $boolean->process();
        $this->assertEmpty($query->clause('where'));
    }
}
