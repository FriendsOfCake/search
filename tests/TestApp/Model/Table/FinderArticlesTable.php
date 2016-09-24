<?php
namespace Search\Test\TestApp\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\Table;

class FinderArticlesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->table('articles');
    }

    public function findActive(Query $query, array $options)
    {
        return $query->where([
                'Articles.is_active' => true
            ] + $options['active']);
    }
}
