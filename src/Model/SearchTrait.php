<?php
declare(strict_types=1);

namespace Search\Model;

use Cake\Datasource\QueryInterface;
use Search\Manager;
use Search\Model\Filter\FilterCollectionInterface;
use Search\Processor;
use function Cake\Core\deprecationWarning;

trait SearchTrait
{
    /**
     * Search Manager instance.
     *
     * @var \Search\Manager|null
     */
    protected ?Manager $_manager = null;

    /**
     * Internal flag to check whether the behavior modified the query.
     *
     * @var bool
     */
    protected bool $_isSearch = false;

    /**
     * Default collection class.
     *
     * @var class-string<\Search\Model\Filter\FilterCollectionInterface>|null
     */
    protected ?string $_collectionClass = null;

    /**
     * Filters processor instance.
     *
     * @var \Search\Processor|null
     */
    protected ?Processor $_processor = null;

    /**
     * Callback fired from the controller.
     *
     * @param \Cake\Datasource\QueryInterface $query Query.
     * @param array $search Array of search arguments.
     * @param string $collection Filter collection name.
     * @return \Cake\Datasource\QueryInterface The Query object used in pagination.
     */
    public function findSearch(
        QueryInterface $query,
        array $search,
        string $collection = Manager::DEFAULT_COLLECTION,
    ): QueryInterface {
        $filters = $this->_getFilters($collection);

        $this->processor()->setEmptyValues($this->_emptyValues());

        $this->_isSearch = $this->processor()->process(
            $filters,
            $query,
            $search,
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
        return $this->_processor ??= new Processor();
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
        return $this->_manager ??= new Manager(
            $this->_repository(),
            $this->_collectionClass,
        );
    }

    /**
     * Gets all filters by the default or given collection from the search manager
     *
     * @param string $collection name of collection
     * @return \Search\Model\Filter\FilterCollectionInterface Filter collection instance.
     */
    protected function _getFilters(string $collection = Manager::DEFAULT_COLLECTION): FilterCollectionInterface
    {
        if (method_exists($this->_repository(), 'searchManager')) {
            deprecationWarning(
                '1.7.1',
                'Support for `searchManager() function on the table class is deprecated.'
                . ' Access the search manager through the Search behavior instance instead or'
                . ' filter collections.',
            );

            return $this->_repository()->searchManager()->getFilters($collection);
        }

        return $this->searchManager()->getFilters($collection);
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
    protected function _emptyValues(): array
    {
        return [];
    }
}
