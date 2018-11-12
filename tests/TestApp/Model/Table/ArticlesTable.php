<?php
namespace Search\Test\TestApp\Model\Table;

use Cake\ORM\Table;

/**
 * @mixin \Search\Model\Behavior\SearchBehavior
 */
class ArticlesTable extends Table
{

    public function initialize(array $config)
    {
        $this->addBehavior('Search.Search');
    }
}
