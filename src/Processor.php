<?php
namespace Search;

use Cake\Datasource\QueryInterface;
use Cake\Utility\Hash;
use Search\Model\Filter\FilterCollectionInterface;

class Processor
{
    /**
     * The filtered and flattend params used for filtering.
     *
     * @var array
     */
    protected $_searchParams = [];

    /**
     * Values that should be treated as empty in search params.
     *
     * @var array
     */
    protected $_emptyValues = ['', false, null];

    /**
     * Processes the given filters.
     *
     * @param \Search\Model\Filter\FilterCollectionInterface $filters The filters to process.
     * @param \Cake\Datasource\QueryInterface $query The query to be modified by the filters.
     * @param array $params The search parameters to pass to the filters.
     * @return bool True is $query was modified by filters else false.
     */
    public function process(FilterCollectionInterface $filters, QueryInterface $query, array $params)
    {
        $params = $this->_flattenParams($params, $filters);
        $params = $this->_extractParams($params, $filters);

        $this->_searchParams = $params;
        $filtered = false;

        foreach ($filters as $filter) {
            $result = $filter($query, $params);
            if ($result !== false) {
                $filtered = true;
            }
        }

        return $filtered;
    }

    /**
     * Set values that should be treated as empty by filters.
     *
     * @param array $emptyValues Values list.
     * @return $this
     */
    public function setEmptyValues(array $emptyValues)
    {
        $this->_emptyValues = $emptyValues;

        return $this;
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
     * Extracts all parameters for which a filter with a matching field
     * name exists.
     *
     * @param array $params The parameters array to extract from.
     * @param \Search\Model\Filter\FilterCollectionInterface $filters Filter collection.
     * @return array The extracted parameters.
     */
    protected function _extractParams(array $params, FilterCollectionInterface $filters)
    {
        $emptyValues = $this->_emptyValues;

        $nonEmptyParams = Hash::filter($params, function ($val) use ($emptyValues) {
            return !in_array($val, $emptyValues, true);
        });

        return array_intersect_key($nonEmptyParams, iterator_to_array($filters));
    }
}
