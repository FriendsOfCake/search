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

    /**
     * @return void
     */
    public function testProcess()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager);
        $filter->args(['title' => 'test']);
        $filter->query($articles->find());
        $filter->process();

        $sql = $filter->query()->sql();
        $this->assertEquals(1, preg_match('/WHERE title like/', $sql));

        $filter->config('comparison', 'ILIKE');
        $filter->query($articles->find());
        $filter->process();

        $sql = $filter->query()->sql();
        $this->assertEquals(1, preg_match('/WHERE title ilike/', $sql));
    }

    /**
     * @return void
     */
    public function testWildCardsEscaping()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager);
        $filter->args(['title' => 'part_1 ? 100% *']);
        $filter->query($articles->find());
        $filter->process();

        $filter->query()->sql();
        $values = $filter->query()->valueBinder()->bindings();
        $value = $values[':c0']['value'];
        $this->assertEquals('part\_1 _ 100\% %', $value);
    }

    /**
     * @return void
     */
    public function testWildcardsBeforeAfter()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, ['before' => true, 'after' => true]);
        $filter->args(['title' => '22% 44_']);
        $filter->query($articles->find());
        $filter->process();

        $filter->query()->sql();
        $values = $filter->query()->valueBinder()->bindings();
        $value = $values[':c0']['value'];
        $this->assertEquals('%22\% 44\_%', $value);
    }

    /**
     * @return void
     */
    public function testWildcardsArray()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like('title', $manager, ['before' => true, 'after' => true]);
        $filter->args(['title' => ['22% 44_']]);
        $filter->query($articles->find());
        $filter->process();

        $filter->query()->sql();
        $values = $filter->query()->valueBinder()->bindings();
        $value = $values[':c0']['value'][0];
        $this->assertEquals('%22\% 44\_%', $value);
    }

    /**
     * @return void
     */
    public function testWildcardsAlternatives()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);

        $filter = new Like(
            'title',
            $manager,
            ['before' => true, 'after' => true, 'wildcardAny' => '%', 'wildcardOne' => '_']);
        $filter->args(['title' => '22% 44_']);
        $filter->query($articles->find());
        $filter->process();

        $filter->query()->sql();
        $values = $filter->query()->valueBinder()->bindings();
        $value = $values[':c0']['value'];
        $this->assertEquals('%22% 44_%', $value);
    }
}
