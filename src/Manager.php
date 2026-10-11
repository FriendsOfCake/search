<?php
declare(strict_types=1);

namespace Search;

use Cake\Core\App;
use Cake\Datasource\RepositoryInterface;
use Cake\Utility\Inflector;
use InvalidArgumentException;
use Search\Model\Filter\FilterCollection;
use Search\Model\Filter\FilterCollectionInterface;
use Search\Model\Filter\FilterMethodsTrait;

/**
 * Search Manager Service Class
 */
class Manager
{
    use FilterMethodsTrait;

    /**
     * Default collection name.
     */
    public const DEFAULT_COLLECTION = 'default';

    /**
     * Repository
     *
     * @var \Cake\Datasource\RepositoryInterface Repository instance
     */
    protected RepositoryInterface $repository;

    /**
     * Filter collections
     *
     * @var array<string, \Search\Model\Filter\FilterCollectionInterface>  Filter collections list.
     */
    protected array $collections = [];

    /**
     * Active filter collection.
     *
     * @var string
     */
    protected string $collectionName = self::DEFAULT_COLLECTION;

    /**
     * Default collection class.
     *
     * @var class-string<\Search\Model\Filter\FilterCollectionInterface>
     */
    protected string $collectionClass = FilterCollection::class;

    /**
     * Constructor
     *
     * @param \Cake\Datasource\RepositoryInterface $repository Repository
     * @param class-string<\Search\Model\Filter\FilterCollectionInterface>|null $collectionClass Default collection class.
     */
    public function __construct(RepositoryInterface $repository, ?string $collectionClass = null)
    {
        $this->repository = $repository;

        if ($collectionClass) {
            $this->collectionClass = $collectionClass;
        }
    }

    /**
     * Return repository instance.
     *
     * @return \Cake\Datasource\RepositoryInterface Repository Instance
     */
    public function getRepository(): RepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Gets all filters in a given collection.
     *
     * @param string $collection Name of the filter collection.
     * @return \Search\Model\Filter\FilterCollectionInterface Filter collection instance.
     * @throws \InvalidArgumentException If requested collection is not set.
     */
    public function getFilters(string $collection = self::DEFAULT_COLLECTION): FilterCollectionInterface
    {
        if (!isset($this->collections[$collection])) {
            $this->collections[$collection] = $this->loadCollection($collection);
        }

        return $this->collections[$collection];
    }

    /**
     * Loads a filter collection.
     *
     * @param string $name Collection name or FQCN.
     * @return \Search\Model\Filter\FilterCollectionInterface
     * @throws \InvalidArgumentException When no filter was found.
     */
    protected function loadCollection(string $name): FilterCollectionInterface
    {
        if ($name === self::DEFAULT_COLLECTION) {
            $class = $this->collectionClass;
        } elseif (strpos($name, '\\') === false) {
            $class = Inflector::camelize(str_replace('-', '_', $name));
        } else {
            $class = $name;
        }

        $className = App::className($class, 'Model/Filter', 'Collection');
        if (!$className) {
            throw new InvalidArgumentException(sprintf(
                'The collection class "%sCollection" does not exist',
                $class,
            ));
        }

        $instance = new $className($this);
        if (!$instance instanceof FilterCollectionInterface) {
            throw new InvalidArgumentException(sprintf(
                'The collection must be instance of FilterCollectionInterface. ' .
                'Got instance of "%s" instead',
                get_class($instance),
            ));
        }

        return $instance;
    }

    /**
     * Sets the filter collection name to use.
     *
     * @param string $name Name of the active filter collection to set.
     * @return $this
     */
    public function useCollection(string $name): static
    {
        $this->collectionName = $name;

        return $this;
    }

    /**
     * Get instance for current collection.
     *
     * @return \Search\Model\Filter\FilterCollectionInterface
     */
    protected function collection(): FilterCollectionInterface
    {
        if (!isset($this->collections[$this->collectionName])) {
            $this->collections[$this->collectionName] = new $this->collectionClass($this);
        }

        return $this->collections[$this->collectionName];
    }

    /**
     * Gets the filter collection name in use currently.
     *
     * @return string The name of the active collection.
     */
    public function getCollectionName(): string
    {
        return $this->collectionName;
    }

    /**
     * Adds a new filter to the active collection.
     *
     * @param string $name Field name.
     * @param string $filter Filter name.
     * @param array $options Filter options.
     * @return $this
     */
    public function add(string $name, string $filter, array $options = []): static
    {
        $this->collection()->add($name, $filter, $options);

        return $this;
    }

    /**
     * Removes filter from the active collection.
     *
     * @param string $name Name of the filter to be removed.
     * @return $this
     */
    public function remove(string $name): static
    {
        $this->collection()->remove($name);

        return $this;
    }
}
