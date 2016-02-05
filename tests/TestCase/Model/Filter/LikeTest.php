<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\Core\Configure;
use Cake\Database\Driver\Postgres;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Model\Filter\Base;
use Search\Model\Filter\Like;

class LikeTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles'
    ];

    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $value = new Like('title', $manager);
        $value->args(['title' => ['test']]);
        $value->query($articles->find());
        $value->process();

        $sql = $value->query()->sql();
        $this->assertEquals(1, preg_match('/WHERE title like/', $sql));

        $value->config('comparison', 'ILIKE');
        $value->query($articles->find());
        $value->process();

        $sql = $value->query()->sql();

        $driver = $value->query()->connection()->driver();

        if ($driver instanceof Postgres) {
            $this->assertEquals(1, preg_match('/WHERE title ilike/', $sql));
        } else {
            $this->assertEquals(1, preg_match('/WHERE title like/', $sql));
        }
    }
}
