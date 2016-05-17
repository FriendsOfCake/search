<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\Core\Configure;
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
        $this->assertEquals(1, preg_match('/WHERE title ilike/', $sql));
    }

    /**
     * @return void
     */
    public function testWildCardsEscaping()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, ['escapeWildcards' => true]);
        $filter->args(['title' => 'part_1 100%']);
        $filter->query($articles->find());
        $filter->process();

        $filter->query()->sql();
        $values = $filter->query()->valueBinder()->bindings();
        $value = $values[':c0']['value'];
        $this->assertEquals('part\_1 100\%', $value);
    }
}
