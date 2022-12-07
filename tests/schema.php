<?php
declare(strict_types=1);

return [
    [
        'table' => 'articles',
        'columns' => [
            'id' => ['type' => 'string', 'null' => false, 'length' => 36],
            'title' => ['type' => 'string', 'null' => false, 'default' => null],
            'number' => ['type' => 'integer', 'null' => true, 'default' => null],
            'is_active' => ['type' => 'boolean', 'null' => false, 'default' => true],
            'created' => ['type' => 'datetime', 'null' => true, 'default' => null],
            'modified' => ['type' => 'datetime', 'null' => true, 'default' => null],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
    [
        'table' => 'comments',
        'columns' => [
            'id' => [
                'type' => 'integer',
            ],
            'article_id' => [
                'type' => 'integer',
                'null' => false,
            ],
            'user_id' => [
                'type' => 'integer',
                'null' => false,
            ],
            'comment' => [
                'type' => 'text',
            ],
            'published' => [
                'type' => 'string',
                'length' => 1,
                'default' => 'N',
            ],
            'created' => [
                'type' => 'datetime',
            ],
            'updated' => [
                'type' => 'datetime',
            ],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
    [
        'table' => 'posts',
        'columns' => [
            'id' => [
                'type' => 'integer',
            ],
            'author_id' => [
                'type' => 'integer',
                'null' => false,
            ],
            'title' => [
                'type' => 'string',
                'null' => false,
            ],
            'body' => 'text',
            'published' => [
                'type' => 'string',
                'length' => 1,
                'default' => 'N',
            ],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
    [
        'table' => 'sections',
        'columns' => [
            'id' => ['type' => 'integer'],
            'title' => ['type' => 'string'],
        ],
        'constraints' => ['primary' => ['type' => 'primary', 'columns' => ['id']]],
    ],
];
