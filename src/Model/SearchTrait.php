<?php
declare(strict_types=1);

namespace Search\Model;

use Cake\Datasource\QueryInterface;
use Cake\Utility\Hash;
use Exception;
use Search\Manager;
use Search\Model\Filter\FilterCollectionInterface;

trait SearchTrait
{
    /**
     * Search Manager instance.
     *
     * @var \Search\Manager|null
     */
    protected $_manager = null;

    /**
     * Internal flag to check whether the behavior modified the query.
     *
     * @var bool
     */
    protected $_isSearch = false;

    /**
     * Params from query string to be used for filtering.
     *
     * @var array
     */
    protected $_searchParams = [];

    /**
     * Default collection class.
     *
     * @var string|null
     */
    protected $_collectionClass;

    /**
     * Callback fired from the controller.
     *
     * @param \Cake\Datasource\QueryInterface $query Query.
     * @param array $options The options for the find.
     *   - `search`: Array of search arguments.
     *   - `collection`: Filter collection name.
     * @return \Cake\Datasource\QueryInterface The Query object used in pagination.
     * @throws \Exception When missing search arguments.
     */
    public function findSearch(QueryInterface $query, array $options)
    {
        if (!isset($options['search'])) {
            throw new Exception(
                'Custom finder "search" expects search arguments ' .
                'to be nested under key "search" in find() options.'
            );
        }

        $filters = $this->_getFilters(Hash::get($options, 'collection', Manager::DEFAULT_COLLECTION));

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
     * Get params from query string to be used for filtering.
     *
     * @return array
     */
    public function searchParams()
    {
        return $this->_searchParams;
    }

    /**
     * Returns the search filter manager.
     *
     * @return \Search\Manager
     */
    public function searchManager()
    {
        if ($this->_manager === null) {
            $this->_manager = new Manager(
                $this->_repository(),
                $this->_collectionClass
            );
        }

        return $this->_manager;
    }

    /**
     * Extracts all parameters for which a filter with a matching field
     * name exists.
     *
     * @param array $params The parameters array to extract from.
     * @param \Search\Model\Filter\FilterCollectionInterface $filters Filter collection.
     * @return array The extracted parameters.
     */
    protected function _extractParams($params, FilterCollectionInterface $filters)
    {
        $emptyValues = $this->_emptyValues();

        $nonEmptyParams = Hash::filter($params, function ($val) use ($emptyValues) {
            return !in_array($val, $emptyValues, true);
        });

        return array_intersect_key($nonEmptyParams, iterator_to_array($filters));
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
     * @param \Search\Model\Filter\FilterCollectionInterface $filters Filter collection instance.
     * @return array The flattened parameters array.
     */
    protected function _flattenParams(array $params, FilterCollectionInterface $filters)
    {
        $flattened = [];
        foreach ($params as $key => $value) {
            if (!is_array($value) ||
                (!empty($filters[$key]) && $filters[$key]->getConfig('flatten') === false)
            ) {
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
     * @param string $collection name of collection
     * @return \Search\Model\Filter\FilterCollectionInterface Filter collection instance.
     */
    protected function _getFilters($collection = Manager::DEFAULT_COLLECTION)
    {
        return $this->_repository()->searchManager()->getFilters($collection);
    }

    /**
     * Processes the given filters.
     *
     * @param \Search\Model\Filter\FilterCollectionInterface $filters The filters to process.
     * @param array $params The parameters to pass to the filters.
     * @param \Cake\Datasource\QueryInterface $query The query to pass to the filters.
     * @return \Cake\Datasource\QueryInterface The query processed by the filters.
     */
    protected function _processFilters(FilterCollectionInterface $filters, $params, $query)
    {
        $this->_searchParams = $params;
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

    /**
     * Returns the repository on which the filters should be applied.
     *
     * @return $this
     */
    protected function _repository()
    {
        return $this;
    }

    /**
     * Return the values which will be seen as empty.
     *
     * @return array
     */
    protected function _emptyValues()
    {
        return ['', false, null];
    }
}
