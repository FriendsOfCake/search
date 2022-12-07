<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

/**
 * @mixin \Search\Model\Behavior\SearchBehavior
 */
class FinderArticlesTable extends Table
{
    /**
     * @param array $config
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('articles');
    }

    /**
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param array $options
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findActive(SelectQuery $query, array $options): SelectQuery
    {
        return $query->where([
                'Articles.is_active' => true,
            ] + $options['active']);
    }

    /**
     * Requires slug key to be present in $options array.
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param array $options
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findSlugged(SelectQuery $query, array $options): SelectQuery
    {
        return $query->where(['title' => $options['slug']]);
    }
}
