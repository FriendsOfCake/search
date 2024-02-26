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
     * @param array $active
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findActive(SelectQuery $query, array $active): SelectQuery
    {
        return $query->where([
                'Articles.is_active' => true,
            ] + $active);
    }

    /**
     * Requires slug key to be present in $options array.
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param string $slug
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findSlugged(SelectQuery $query, string $slug): SelectQuery
    {
        return $query->where(['title' => $slug]);
    }

    /**
     * Requires nullable slug key to be present in $options array.
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param string|null $slug
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findSluggedNullable(SelectQuery $query, ?string $slug): SelectQuery
    {
        return $query->where(['title IS' => $slug]);
    }

    /**
     * Requires uid key to be present in $options array.
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param int $uid
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findUser(SelectQuery $query, int $uid): SelectQuery
    {
        return $query->where(['user_id' => $uid]);
    }

    /**
     * Requires nullable uid key to be present in $options array.
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param int|null $uid
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findUserNullable(SelectQuery $query, ?int $uid): SelectQuery
    {
        return $query->where(['user_id IS' => $uid]);
    }
}
