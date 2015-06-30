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
        'implementendMethods' => [
            'filterParams' => 'filterParams'
        ]
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

    /**
     * Returns the valid search parameter values according to those that are defined
     * in the searchConfiguration() method of the table.
     *
     * @param array $param a key value list of search parameters to use for a search.
     * @return array
     */
    public function filterParams($params)
    {
        $blacklist = [
            'fields' => 1,
            'conditions' => 1,
            'join' => 1,
            'order' => 1,
            'limit' => 1,
            'offset' => 1,
            'group' => 1,
            'having' => 1,
            'contain' => 1,
            'page' => 1,
        ];

        $params = array_diff_key($params, $blacklist);
        $valid = $this->_table->searchConfiguration()->all();

        return array_intersect_key($params, $valid);
    }
}
