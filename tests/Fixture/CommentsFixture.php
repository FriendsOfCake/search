<?php
namespace Search\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class CommentsFixture extends TestFixture
{

    /**
     * Fields
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'string', 'null' => false, 'length' => 36],
        'article_id' => ['type' => 'string', 'null' => false, 'length' => 36],
        'body' => ['type' => 'string', 'null' => false, 'default' => null],
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
            'article_id' => '1',
            'body' => 'Hello World!',
            'created' => '2012-12-12 12:12:12',
            'modified' => '2013-01-01 11:11:11',
        ],
    ];
}
