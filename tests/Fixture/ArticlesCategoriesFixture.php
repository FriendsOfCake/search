<?php
namespace Search\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Short description for class.
 */
class ArticlesCategoriesFixture extends TestFixture
{
    /**
     * fields property
     *
     * @var array
     */
    public $fields = [
        'article_id' => ['type' => 'integer', 'null' => false],
        'category_id' => ['type' => 'integer', 'null' => false],
        '_constraints' => [
            'unique_tag' => ['type' => 'primary', 'columns' => ['article_id', 'category_id']],
            'category_id_fk' => [
                'type' => 'foreign',
                'columns' => ['category_id'],
                'references' => ['categories', 'id'],
                'update' => 'cascade',
                'delete' => 'cascade',
            ],
        ],
    ];

    /**
     * records property
     *
     * @var array
     */
    public $records = [
        ['article_id' => 1, 'category_id' => 1],
        ['article_id' => 1, 'category_id' => 2],
        ['article_id' => 2, 'category_id' => 1],
        ['article_id' => 2, 'category_id' => 3],
    ];
}
