<?php
namespace FOC\Search\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture {

/**
 * Fields
 *
 * @var array
 */
    public $fields = [
        'id' => ['type' => 'string', 'null' => false, 'length' => 36],
        'title' => ['type' => 'string', 'null' => false, 'default' => null],
        'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
        'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id']],
        ]
    ];

/**
 * Records
 *
 * @var array
 */
    public $records = [
        [
            'id' => '1',
            'title' => 'Test title one',
            'created' => '2012-12-12 12:12:12',
            'modified' => '2013-01-01 11:11:11',
        ],
    ];
}
