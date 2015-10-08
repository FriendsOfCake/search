<?php
namespace Search\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Search\Manager;

class SearchBehavior extends Behavior
{

    /**
     * $_defaultConfig For the Behavior.
     *
     * ### Options
     * - `searchConfigMethod` Method name of the method that returns the filters.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'searchConfigMethod' => 'searchConfiguration',
        'implementedFinders' => [
            'search' => 'findSearch'
        ],
        'implementendMethods' => [
            'filterParams' => 'filterParams',
            'searchManager' => 'searchManager'
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
        if (isset($options['search'])) {
            $options = $options['search'];
        }

        foreach ($this->_table->{$this->config('searchConfigMethod')}()->all() as $config) {
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
     * @param array $params a key value list of search parameters to use for a search.
     * @return array
     */
    public function filterParams($params)
    {
        $valid = $this->_table->{$this->config('searchConfigMethod')}()->all();
        return ['search' => array_intersect_key($params, $valid)];
    }

    /**
     * Returns the search filter manager.
     *
     * @return \Search\Manager;
     */
    public function searchManager()
    {
        if (empty($this->_manager)) {
            $this->_manager = new Manager($this->_table);
        }
        return $this->_manager;
    }
}
