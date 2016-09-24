<?php
namespace Search\Test\TestApp\Model\Table;

use Cake\ORM\Table;
use Search\Manager;

class GroupsTable extends Table
{

    public function searchConfiguration()
    {
        $manager = new Manager($this);

        return $manager
            ->collection('frontend')
            ->value('title')
            ->collection('backend')
            ->like('title', ['before' => true, 'after' => true]);
    }
}
