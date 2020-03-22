<?php
declare(strict_types=1);

namespace Search\Model;

use Cake\Datasource\QueryInterface;
use Cake\Utility\Hash;
use Exception;
use Search\Manager;
use Search\Model\Filter\FilterCollectionInterface;
use Search\Processor;

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
     * Default collection class.
     *
     * @var string|null
     * @psalm-var class-string<\Search\Model\Filter\FilterCollectionInterface>|null
     */
    protected $_collectionClass;

    /**
     * Filters processor instance.
     *
     * @var \Search\Processor|null
     */
    protected $_processor;

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
    public function findSearch(QueryInterface $query, array $options): QueryInterface
    {
        if (!isset($options['search'])) {
            throw new Exception(
                'Custom finder "search" expects search arguments ' .
                'to be nested under key "search" in find() options.'
            );
        }

        $filters = $this->_getFilters(Hash::get($options, 'collection', Manager::DEFAULT_COLLECTION));

        $emptyValues = $this->_emptyValues();
        if ($emptyValues !== null) {
            $this->processor()->setEmptyValues($emptyValues);
        }

        $this->_isSearch = $this->processor()->process(
            $filters,
            $query,
            (array)$options['search']
        );

        return $query;
    }

    /**
     * Get filters processor instance.
     *
     * @return \Search\Processor
     */
    public function processor(): Processor
    {
        if ($this->_processor === null) {
            $this->_processor = new Processor();
        }

        return $this->_processor;
    }

    /**
     * Returns true if the findSearch call modified the query in a way
     * that at least one search filter has been applied.
     *
     * @return bool
     */
    public function isSearch(): bool
    {
        return $this->_isSearch;
    }

    /**
     * Get params from query string to be used for filtering.
     *
     * @return array
     */
    public function searchParams(): array
    {
        return $this->processor()->searchParams();
    }

    /**
     * Returns the search filter manager.
     *
     * @return \Search\Manager
     */
    public function searchManager(): Manager
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
     * Gets all filters by the default or given collection from the search manager
     *
     * @param string $collection name of collection
     * @return \Search\Model\Filter\FilterCollectionInterface Filter collection instance.
     */
    protected function _getFilters(string $collection = Manager::DEFAULT_COLLECTION): FilterCollectionInterface
    {
        return $this->_repository()->searchManager()->getFilters($collection);
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
     * @return array|null
     */
    protected function _emptyValues(): ?array
    {
        return null;
    }
}
