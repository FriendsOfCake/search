<?php
namespace Search\Test\TestApp\Model\Table;

use Cake\ORM\Query;
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
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('articles');
    }

    /**
     * @param \Cake\ORM\Query $query
     * @param array $options
     *
     * @return \Cake\ORM\Query
     */
    public function findActive(Query $query, array $options)
    {
        return $query->where([
                'Articles.is_active' => true
            ] + $options['active']);
    }

    /**
     * Requires slug key to be present in $options array.
     *
     * @param \Cake\ORM\Query $query
     * @param array $options
     *
     * @return \Cake\ORM\Query
     */
    public function findSlugged(Query $query, array $options)
    {
        return $query->where(['title' => $options['slug']]);
    }
}
