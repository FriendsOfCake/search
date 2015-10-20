<?php
namespace Search\Test\TestCase\Type;

use Cake\Core\Configure;
use Cake\Database\Expression\FunctionExpression;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Search\Manager;
use Search\Type\Like;

class LikeTest extends TestCase
{

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Users'
    ];

    public function testProcess()
    {
        $users = TableRegistry::get('Users');
        $manager = new Manager($users);

        $value = new Like('username', $manager, [
            'before' => true,
            'after' => true,
            'field' => [
                $users->aliasField('username')
            ]
        ]);
        $value->args(['username' => 'erTe']);
        $value->query($users->find());
        $value->process();

        $this->assertEquals(1, $value->query()->count());

        $value = new Like('username', $manager, [
            'field' => [
                $users->aliasField('username')
            ]
        ]);
        $value->args(['username' => 'UserTest']);
        $value->query($users->find());
        $value->process();

        $this->assertEquals(1, $value->query()->count());

        $value = new Like('username', $manager, [
            'before' => true,
            'field' => [
                $users->aliasField('username')
            ]
        ]);
        $value->args(['username' => 'Test']);
        $value->query($users->find());
        $value->process();

        $this->assertEquals(1, $value->query()->count());

        $value = new Like('username', $manager, [
            'before' => true,
            'after' => true,
            'field' => [
                $users->aliasField('username')
            ]
        ]);
        $value->args(['username' => 'Test']);
        $value->query($users->find());
        $value->process();

        $this->assertEquals(2, $value->query()->count());
    }

    public function testProcessWithFunctionExpression()
    {
        $users = TableRegistry::get('Users');
        $manager = new Manager($users);

        $concatExpression = new FunctionExpression('CONCAT', [
            $users->aliasField('firstname') => 'literal',
            ' ',
            $users->aliasField('lastname') => 'literal'
        ]);

        $value = new Like('name', $manager, [
            'before' => true,
            'after' => true,
            'field' => [
                $concatExpression
            ]
        ]);
        $value->args(['name' => 'Test User']);
        $value->query($users->find());
        $value->process();

        $this->assertEquals(1, $value->query()->count());

        $value = new Like('name', $manager, [
            'before' => true,
            'after' => true,
            'field' => [
                $concatExpression
            ]
        ]);
        $value->args(['name' => 'User']);
        $value->query($users->find());
        $value->process();

        $this->assertEquals(3, $value->query()->count());

        $value = new Like('name', $manager, [
            'after' => true,
            'field' => [
                $concatExpression
            ]
        ]);
        $value->args(['name' => 'Test']);
        $value->query($users->find());
        $value->process();

        $this->assertEquals(1, $value->query()->count());
    }
}
