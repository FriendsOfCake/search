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
     * Search Manager instance.
     *
     * @var \Search\Manager
     */
    public $_manager = null;

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
        if (!isset($options['_search']) && (!isset($options['_filter']) || $options['_filter'] === true)) {
            $options = $this->filterParams($options);
        }

        if (isset($options['_search'])) {
            $options = $options['_search'];
        }

        $filters = $this->_getAllFilters();
        foreach ($filters as $config) {
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
        return ['_search' => array_intersect_key($params, $this->_getAllFilters())];
    }

    /**
     * Returns the search filter manager.
     *
     * @return \Search\Manager
     */
    public function searchManager()
    {
        if (empty($this->_manager)) {
            $this->_manager = new Manager($this->_table);
        }
        return $this->_manager;
    }

    /**
     * Gets all filters from the search manager.
     *
     * @return array An array of filters for the defined fields.
     */
    protected function _getAllFilters()
    {
        $method = $this->config('searchConfigMethod');
        if (method_exists($this->_table, $method)) {
            return $this->_table->{$method}()->all();
        }
        return $this->searchManager()->all();
    }
}
