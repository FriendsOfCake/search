<?php
namespace Search\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\Utility\Hash;
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
     * Default config for the behavior.
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
        'implementedMethods' => [
            'filterParams' => 'filterParams',
            'searchManager' => 'searchManager'
        ]
    ];

    /**
     * Callback fired from the controller.
     *
     * @param Query $query Query.
     * @param array $options The options for the find.
     *   - `_search`: If set it's value will be used as search arguments else
     *     $options itself will be used.
     * @return \Cake\ORM\Query The Query object used in pagination.
     */
    public function findSearch(Query $query, array $options)
    {
        $params = $options;
        if (isset($params['_search'])) {
            $params = $params['_search'];
        }

        $filters = $this->_getAllFilters();
        $params = array_intersect_key(Hash::filter($params), $filters);

        foreach ($filters as $filter) {
            $filter->args($params);
            $filter->query($query);
            $filter->process();
        }

        return $query;
    }

    /**
     * Returns the search filter manager.
     *
     * @return \Search\Manager
     */
    public function searchManager()
    {
        if ($this->_manager === null) {
            $this->_manager = new Manager($this->_table);
        }
        return $this->_manager;
    }

    /**
     * Gets all filters from the search manager.
     *
     * @return \Search\Model\Filter\Base[] An array of filters for the defined fields.
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
