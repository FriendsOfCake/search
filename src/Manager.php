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
    protected $_collection = 'default';

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
        $this->_collections['default'] = new $this->_collectionClass($this);
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
     * @return array Array of filter instances.
     * @throws \InvalidArgumentException If requested collection is not set.
     */
    public function getFilters($collection = 'default')
    {
        if (!isset($this->_collections[$collection])) {
            $this->_collections[$collection] = $this->_loadCollection($collection);
        }

        if ($this->_collections[$collection] instanceof FilterCollectionInterface) {
            return $this->_collections[$collection]->toArray();
        }

        return $this->_collections[$collection];
    }

    /**
     * Loads a filter collection.
     *
     * @param string $name Collection name.
     * @return \Search\Model\Filter\FilterCollectionInterface
     * @throws \InvalidArgumentException When no filter was found.
     */
    protected function _loadCollection($name)
    {
        $class = Inflector::camelize($name);

        $className = App::className($class, 'Model/Filter', 'Collection');
        if (!$className) {
            throw new InvalidArgumentException(sprintf(
                'The collection class "%sCollection" does not exist',
                $class
            ));
        }

        return new $className($this);
    }

    /**
     * Sets the filter collection name to use.
     *
     * @param string $name Name of the active filter collection to set.
     * @return $this
     */
    public function useCollection($name)
    {
        if (!isset($this->_collections[$name])) {
            $this->_collections[$name] = new $this->_collectionClass($this);
        }
        $this->_collection = $name;

        return $this;
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
        $this->_collections[$this->_collection]->add($name, $filter, $options);

        return $this;
    }

    /**
     * Removes filter from the active collection.
     *
     * @param string $name Name of the filter to be removed.
     * @return void
     */
    public function remove($name)
    {
        unset($this->_collections[$this->_collection][$name]);
    }
}
