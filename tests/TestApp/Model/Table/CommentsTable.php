<?php
namespace Search\Test\TestApp\Model\Table;

use Cake\ORM\Table;
use Search\Manager;

class CommentsTable extends Table
{

    public function searchConfiguration()
    {
        $manager = new Manager($this);

        return $manager
            ->value('Comments.foo')
            ->like('Comments.search', ['filterEmpty' => true, 'multiValue' => true])
            ->value('Comments.baz')
            ->value('Comments.group', ['field' => 'Comments.group'])
            ->value('group', ['multiValue' => true])
            ->value('published');
    }
}
