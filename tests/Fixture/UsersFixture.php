<?php
namespace Search\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class UsersFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'string', 'null' => false, 'length' => 36],
        'username' => ['type' => 'string', 'null' => false, 'default' => null],
        'firstname' => ['type' => 'string', 'null' => false, 'default' => null],
        'lastname' => ['type' => 'string', 'null' => false, 'default' => null],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
    ];

    /**
     * Records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '1',
            'username' => 'UserTest',
            'firstname' => 'User',
            'lastname' => 'Test',
            'created' => '2012-12-12 12:12:12',
            'modified' => '2013-01-01 11:11:11',
        ],
        [
            'id' => '2',
            'username' => 'TestUser',
            'firstname' => 'Test',
            'lastname' => 'User',
            'created' => '2014-11-01 21:12:42',
            'modified' => '2015-08-12 11:43:12',
        ],
        [
            'id' => '3',
            'username' => 'ASecondUser',
            'firstname' => 'Second',
            'lastname' => 'User',
            'created' => '2014-11-01 21:12:42',
            'modified' => '2015-08-12 11:43:12',
        ]
    ];
}
