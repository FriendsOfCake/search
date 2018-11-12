<?php
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
    const DEFAULT_COLLECTION = 'default';

    /**
     * Repository
     *
     * @var \Cake\Datasource\RepositoryInterface Repository instance
     */
    protected $_repository;

    /**
     * Filter collections
     *
     * @var \Search\Model\Filter\FilterCollectionInterface[] Filter collections list.
     */
    protected $_collections = [];

    /**
     * Active filter collection.
     *
     * @var string
     */
    protected $_collection = self::DEFAULT_COLLECTION;

    /**
     * Default collection class.
     *
     * @var string
     */
    protected $_collectionClass = FilterCollection::class;

    /**
     * Constructor
     *
     * @param \Cake\Datasource\RepositoryInterface $repository Repository
     * @param string|null $collectionClass Default collection class.
     */
    public function __construct(RepositoryInterface $repository, $collectionClass = null)
    {
        $this->_repository = $repository;

        if ($collectionClass) {
            $this->_collectionClass = $collectionClass;
        }
    }

    /**
     * Return repository instance.
     *
     * @return \Cake\Datasource\RepositoryInterface Repository Instance
     */
    public function getRepository()
    {
        return $this->_repository;
    }

    /**
     * Gets all filters in a given collection.
     *
     * @param string $collection Name of the filter collection.
     * @return \Search\Model\Filter\FilterCollectionInterface Filter collection instance.
     * @throws \InvalidArgumentException If requested collection is not set.
     */
    public function getFilters($collection = self::DEFAULT_COLLECTION)
    {
        if (!isset($this->_collections[$collection])) {
            $this->_collections[$collection] = $this->_loadCollection($collection);
        }

        return $this->_collections[$collection];
    }

    /**
     * Loads a filter collection.
     *
     * @param string $name Collection name or FQCN.
     * @return \Search\Model\Filter\FilterCollectionInterface
     * @throws \InvalidArgumentException When no filter was found.
     */
    protected function _loadCollection($name)
    {
        if ($name === self::DEFAULT_COLLECTION) {
            $class = $this->_collectionClass;
        } else {
            $class = Inflector::camelize(str_replace('-', '_', $name));
        }

        $className = App::className($class, 'Model/Filter', 'Collection');
        if (!$className) {
            throw new InvalidArgumentException(sprintf(
                'The collection class "%sCollection" does not exist',
                $class
            ));
        }

        $instance = new $className($this);
        if (!$instance instanceof FilterCollectionInterface) {
            throw new InvalidArgumentException(sprintf(
                'The collection must be instance of FilterCollectionInterface. ' .
                'Got instance of "%s" instead',
                get_class($instance)
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
    public function useCollection($name)
    {
        $this->_collection = $name;

        return $this;
    }

    /**
     * Get instance for current collection.
     *
     * @return \Search\Model\Filter\FilterCollectionInterface
     */
    protected function _collection()
    {
        if (!isset($this->_collections[$this->_collection])) {
            $this->_collections[$this->_collection] = new $this->_collectionClass($this);
        }

        return $this->_collections[$this->_collection];
    }

    /**
     * Gets the filter collection name in use currently.
     *
     * @return string The name of the active collection.
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Adds a new filter to the active collection.
     *
     * @param string $name Field name.
     * @param string $filter Filter name.
     * @param array $options Filter options.
     * @return $this
     */
    public function add($name, $filter, array $options = [])
    {
        $this->_collection()->add($name, $filter, $options);

        return $this;
    }

    /**
     * Removes filter from the active collection.
     *
     * @param string $name Name of the filter to be removed.
     * @return $this
     */
    public function remove($name)
    {
        $this->_collection()->remove($name);

        return $this;
    }
}
