<?php
declare(strict_types=1);

namespace Search\Model;

use Cake\Datasource\QueryInterface;
use Cake\Datasource\RepositoryInterface;
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
    protected ?Manager $manager = null;

    /**
     * Internal flag to check whether the behavior modified the query.
     *
     * @var bool
     */
    protected bool $isSearch = false;

    /**
     * Default collection class.
     *
     * @var class-string<\Search\Model\Filter\FilterCollectionInterface>|null
     */
    protected ?string $collectionClass = null;

    /**
     * Filters processor instance.
     *
     * @var \Search\Processor|null
     */
    protected ?Processor $processor = null;

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
        $filters = $this->getFilters($collection);

        $this->processor()->setEmptyValues($this->emptyValues());

        $this->isSearch = $this->processor()->process(
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
        return $this->processor ??= new Processor();
    }

    /**
     * Returns true if the findSearch call modified the query in a way
     * that at least one search filter has been applied.
     *
     * @return bool
     */
    public function isSearch(): bool
    {
        return $this->isSearch;
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
        return $this->manager ??= new Manager(
            $this->repository(),
            $this->collectionClass,
        );
    }

    /**
     * Gets all filters by the default or given collection from the search manager
     *
     * @param string $collection name of collection
     * @return \Search\Model\Filter\FilterCollectionInterface Filter collection instance.
     */
    protected function getFilters(string $collection = Manager::DEFAULT_COLLECTION): FilterCollectionInterface
    {
        if (method_exists($this->repository(), 'searchManager')) {
            deprecationWarning(
                '7.7.1',
                'Support for `searchManager() function on the table class is deprecated.'
                . ' Access the search manager through the Search behavior instance instead or'
                . ' filter collections.',
            );

            return $this->repository()->searchManager()->getFilters($collection);
        }

        return $this->searchManager()->getFilters($collection);
    }

    /**
     * Returns the repository on which the filters should be applied.
     *
     * @return \Cake\Datasource\RepositoryInterface
     */
    protected function repository(): RepositoryInterface
    {
        return $this;
    }

    /**
     * Return the values which will be seen as empty.
     *
     * @return array
     */
    protected function emptyValues(): array
    {
        return [];
    }
}
