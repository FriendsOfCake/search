<?php
namespace Search\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Short description for class.
 */
class CategoriesFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'id' => ['type' => 'integer'],
        'name' => ['type' => 'string', 'null' => false],
        'created' => 'datetime',
        'updated' => 'datetime',
        '_constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['name' => 'Category 1', 'created' => '2022-05-28 15:30:23', 'updated' => '2022-05-28 15:30:24'],
        ['name' => 'Category 2', 'created' => '2022-05-28 15:30:23', 'updated' => '2022-05-28 15:30:24'],
        ['name' => 'Category 3', 'created' => '2022-05-28 15:30:23', 'updated' => '2022-05-28 15:30:24'],
    ];
}
