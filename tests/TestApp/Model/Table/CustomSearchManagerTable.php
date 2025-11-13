<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Model\Table;

use Cake\ORM\Table;
use Search\Manager;

/**
 * Table with custom searchManager() method to test that it gets called.
 *
 * @mixin \Search\Model\Behavior\SearchBehavior
 */
class CustomSearchManagerTable extends Table
{
    /**
     * Tracks whether the custom searchManager() method was called.
     *
     * @var bool
     */
    public static $searchManagerCalled = false;

    public function initialize(array $config): void
    {
        $this->setTable('articles');
        $this->addBehavior('Search.Search');
    }

    /**
     * Custom searchManager() implementation that should be called.
     *
     * @return \Search\Manager
     */
    public function searchManager(): Manager
    {
        static::$searchManagerCalled = true;

        $searchManager = $this->behaviors()->Search->searchManager()
            ->value('title');

        return $searchManager;
    }
}
