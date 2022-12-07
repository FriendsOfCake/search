<?php
namespace Search\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class ArticlesFixture extends TestFixture
{
    /**
     * Records
     *
     * @var array
     */
    public array $records = [
        [
            'id' => '1',
            'title' => 'Test title one',
            'number' => '123456',
            'is_active' => true,
            'created' => '2012-12-12 12:12:12',
            'modified' => '2013-01-01 11:11:11',
        ],
    ];
}
