<?php
declare(strict_types=1);
namespace Search\Test\TestApp\Model\Table;

use Cake\ORM\Table;

/**
 * @mixin \Search\Model\Behavior\SearchBehavior
 */
class GroupsTable extends Table
{
    public function initialize(array $config): void
    {
        $this->addBehavior('Search.Search');

        $this->searchManager()
            ->useCollection('frontend')
            ->value('title')
            ->useCollection('backend')
            ->like('title', ['before' => true, 'after' => true]);
    }
}
