<?php
namespace Search\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;

class SearchBehavior extends Behavior
{

    /**
     * $_defaultConfig For the Behavior.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'implementedFinders' => [
            'search' => 'findSearch'
        ],
    ];

    /**
     * Callback fired from the controller.
     *
     * @param Query $query Query.
     * @param array $options The GET arguments.
     * @return \Cake\ORM\Query The Query object used in pagination.
     */
    public function findSearch(Query $query, array $options)
    {
        foreach ($this->_table->searchConfiguration()->all() as $config) {
            $config->args($options);
            $config->query($query);
            $config->process();
        }

        return $query;
    }
}
