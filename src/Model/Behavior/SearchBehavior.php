<?php
namespace Search\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Exception;
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
            'searchManager' => 'searchManager',
            'isSearch' => 'isSearch'
        ],
    ];

    /**
     * Internal flag to check whether the behavior modified the query.
     *
     * @var bool
     */
    protected $_isSearch = false;

    /**
     * Callback fired from the controller.
     *
     * @param Query $query Query.
     * @param array $options The options for the find.
     *   - `search`: Array of search arguments.
     *   - `collection`: Filter collection name.
     * @return \Cake\ORM\Query The Query object used in pagination.
     * @throws \Exception When missing search arguments.
     */
    public function findSearch(Query $query, array $options)
    {
        if (!isset($options['search'])) {
            throw new Exception(
                'Custom finder "search" expects search arguments ' .
                'to be nested under key "search" in find() options.'
            );
        }

        $filters = $this->_getAllFilters(Hash::get($options, 'collection', 'default'));

        $params = $this->_flattenParams((array)$options['search'], $filters);
        $params = $this->_extractParams($params, $filters);

        return $this->_processFilters($filters, $params, $query);
    }

    /**
     * Returns true if the findSearch call modified the query in a way
     * that at least one search filter has been applied.
     *
     * @return bool
     */
    public function isSearch()
    {
        return $this->_isSearch;
    }

    /**
     * Returns search params nested in array with key `_search` for passing as
     * options to find method.
     *
     * @param array $params A key value list of search parameters to use for a search.
     * @return array
     * @deprecated 2.0.0 You can directly call find like
     *   `find('search', ['search' => $this->request->query])`
     */
    public function filterParams($params)
    {
        return ['search' => $params];
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
     * Extracts all parameters for wich a filter with a matching field
     * name exists.
     *
     * @param array $params The parameters array to extract from.
     * @param \Search\Model\Filter\Base[] $filters The filters to match against.
     * @return array The extracted parameters.
     */
    protected function _extractParams($params, $filters)
    {
        return array_intersect_key(Hash::filter($params), $filters);
    }

    /**
     * Flattens a parameters array, so that possible aliased parameter
     * keys that are provided in a nested fashion, are being grouped
     * using flat keys.
     *
     * ### Example:
     *
     * The following parameters array:
     *
     * ```
     * [
     *     'Alias' => [
     *         'field' => 'value'
     *         'otherField' => [
     *             'value',
     *             'otherValue'
     *         ]
     *     ],
     *     'field' => 'value'
     * ]
     * ```
     *
     * would return as
     *
     * ```
     * [
     *     'Alias.field' => 'value',
     *     'Alias.otherField' => [
     *         'value',
     *         'otherValue'
     *     ],
     *     'field' => 'value'
     * ]
     * ```
     *
     * @param array $params The parameters array to flatten.
     * @param array $filters The array of filters with configuration
     * @return array The flattened parameters array.
     */
    protected function _flattenParams($params, $filters)
    {
        $flattened = [];
        foreach ($params as $key => $value) {
            if (is_array($value) && !empty($filters[$key]) && $filters[$key]->getConfig()['flatten'] == false) {
                $flattened[$key] = $value;
                continue;
            }

            if (!is_array($value)) {
                $flattened[$key] = $value;
                continue;
            }

            foreach ($value as $childKey => $childValue) {
                if (!is_numeric($childKey)) {
                    $flattened[$key . '.' . $childKey] = $childValue;
                } else {
                    $flattened[$key][$childKey] = $childValue;
                }
            }
        }

        return $flattened;
    }

    /**
     * Gets all filters by the default or given collection from the search manager
     *
     * @param string|null $collection name of collection
     * @return \Search\Model\Filter\Base[] An array of filters for the defined fields.
     */
    protected function _getAllFilters($collection = 'default')
    {
        $method = $this->config('searchConfigMethod');
        if (method_exists($this->_table, $method)) {
            $manager = $this->_table->{$method}();
        } else {
            $manager = $this->searchManager();
        }

        return $manager->getFilters($collection);
    }

    /**
     * Processes the given filters.
     *
     * @param \Search\Model\Filter\Base[] $filters The filters to process.
     * @param array $params The parameters to pass to the filters.
     * @param \Cake\ORM\Query $query The query to pass to the filters.
     * @return \Cake\ORM\Query The query processed by the filters.
     */
    protected function _processFilters($filters, $params, $query)
    {
        $this->_isSearch = false;
        foreach ($filters as $filter) {
            $filter->setArgs($params);
            $filter->setQuery($query);

            if ($filter->skip()) {
                continue;
            }
            $result = $filter->process();
            if ($result !== false) {
                $this->_isSearch = true;
            }
        }

        return $query;
    }
}
