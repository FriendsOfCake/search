<?php
namespace Search\Test\TestApp\Model\Table;

use Cake\ORM\Table;
use Search\Manager;

/**
 * @mixin \Search\Model\Behavior\SearchBehavior
 */
class GroupsTable extends Table
{

    public function searchConfiguration()
    {
        $manager = new Manager($this);

        return $manager
            ->useCollection('frontend')
            ->value('title')
            ->useCollection('backend')
            ->like('title', ['before' => true, 'after' => true]);
    }
}
