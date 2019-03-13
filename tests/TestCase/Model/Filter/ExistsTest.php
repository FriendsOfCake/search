<?php
namespace Search\Test\TestCase\Model\Filter;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Cake\Utility\Hash;
use Search\Manager;
use Search\Model\Filter\Exists;

class ExistsTest extends TestCase
{
    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles',
    ];

    /**
     * @return void
     */
    public function testProcessWithFlagOn()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Exists('number', $manager);
        $filter->setArgs(['number' => '1']);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        $this->assertTrue($result);

        $this->assertRegExp(
            '/WHERE \(Articles\.number\) IS NOT NULL$/',
            $filter->getQuery()->sql()
        );
    }

    /**
     * @return void
     */
    public function testProcessWithFlagOff()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Exists('number', $manager);
        $filter->setArgs(['number' => '0']);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        $this->assertTrue($result);

        $this->assertRegExp(
            '/WHERE \(Articles\.number\) IS NULL$/',
            $filter->getQuery()->sql()
        );
    }

    /**
     * @return void
     */
    public function testProcessWithFlagOnNotNullable()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Exists('number', $manager);
        $filter->setConfig('nullValue', '');

        $filter->setArgs(['number' => 1]);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        $this->assertTrue($result);

        $this->assertRegExp(
            '/WHERE Articles\.number != \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [''],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithFlagOffNotNullable()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Exists('number', $manager);
        $filter->setConfig('nullValue', '');

        $filter->setArgs(['number' => 0]);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        $this->assertTrue($result);

        $this->assertRegExp(
            '/WHERE Articles\.number = \:c0$/',
            $filter->getQuery()->sql()
        );
        $this->assertEquals(
            [''],
            Hash::extract($filter->getQuery()->getValueBinder()->bindings(), '{s}.value')
        );
    }

    /**
     * @return void
     */
    public function testProcessWithStringDisabled()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Exists('number', $manager);
        $filter->setArgs(['number' => '']);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        $this->assertFalse($result);
    }

    /**
     * @return void
     */
    public function testProcessMultiValueSafe()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Exists('number', $manager, ['multiValue' => true]);
        $filter->setArgs(['number' => [0, 1]]);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        $this->assertFalse($result);

        $this->assertEmpty($filter->getQuery()->clause('where'));
        $filter->getQuery()->sql();
        $this->assertEmpty($filter->getQuery()->getValueBinder()->bindings());
    }

    /**
     * @return void
     */
    public function testProcessMultiField()
    {
        $articles = TableRegistry::get('Articles');
        $manager = new Manager($articles);
        $filter = new Exists('exists', $manager, [
            'field' => ['number', 'title'],
        ]);
        $filter->setArgs(['exists' => true]);
        $filter->setQuery($articles->find());
        $result = $filter->process();

        $this->assertTrue($result);

        $this->assertRegExp(
            '/WHERE \(\(Articles\.number\) IS NOT NULL OR \(Articles\.title\) IS NOT NULL\)$/',
            $filter->getQuery()->sql()
        );
    }
}
