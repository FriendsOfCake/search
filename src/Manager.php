<?php
namespace Search;

use Cake\Core\App;
use Cake\Datasource\RepositoryInterface;
use Cake\Utility\Inflector;
use InvalidArgumentException;

class Manager
{

    /**
     * Repository
     *
     * @var \Cake\Datasource\RepositoryInterface Repository instance
     */
    protected $_repository;

    /**
     * Filter collection and their filters
     *
     * @var array
     */
    protected $_filters = [
        'default' => []
    ];

    /**
     * Active filter collection.
     *
     * @var string
     */
    protected $_collection = 'default';

    /**
     * Constructor
     *
     * @param \Cake\Datasource\RepositoryInterface $repository Repository
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->_repository = $repository;
    }

    /**
     * Return all configured types.
     *
     * @return array Config
     */
    public function all()
    {
        return $this->_filters['default'];
    }

    /**
     * Return repository instance.
     *
     * @return \Cake\Datasource\RepositoryInterface Repository Instance
     */
    public function repository()
    {
        return $this->_repository;
    }

    /**
     * Return Table
     *
     * @return \Cake\Datasource\RepositoryInterface Repository Instance
     * @deprecated Use repository() instead.
     */
    public function table()
    {
        return $this->repository();
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
        if (!isset($this->_filters[$collection])) {
            throw new InvalidArgumentException(
                sprintf('The collection "%s" does not exist.', $collection)
            );
        }

        return $this->_filters[$collection];
    }

    /**
     * Sets the filter collection name to use.
     *
     * @param string $name Name of the active filter collection to set.
     * @return $this
     */
    public function useCollection($name)
    {
        if (!isset($this->_filters[$name])) {
            $this->_filters[$name] = [];
        }
        $this->_collection = $name;

        return $this;
    }

    /**
     * Sets or gets the filter collection name.
     *
     * @return string The name of the active collection.
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    /**
     * Sets or gets the filter collection name.
     *
     * @deprecated 3.0.0 Use addCollection()/getCollection() instead.
     * @param string|null $name Name of the active filter collection to set.
     * @return string|$this Returns $this or the name of the active collection if no $name was provided.
     */
    public function collection($name = null)
    {
        if ($name === null) {
            return $this->getCollection();
        }

        return $this->useCollection($name);
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
        $this->_filters[$this->_collection][$name] = $this->loadFilter($name, $filter, $options);

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
        unset($this->_filters[$this->_collection][$name]);
    }

    /**
     * boolean method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function boolean($name, array $config = [])
    {
        $this->add($name, 'Search.Boolean', $config);

        return $this;
    }

    /**
     * like method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function like($name, array $config = [])
    {
        $this->add($name, 'Search.Like', $config);

        return $this;
    }

    /**
     * value method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function value($name, array $config = [])
    {
        $this->add($name, 'Search.Value', $config);

        return $this;
    }

    /**
     * finder method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function finder($name, array $config = [])
    {
        $this->add($name, 'Search.Finder', $config);

        return $this;
    }

    /**
     * callback method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function callback($name, array $config = [])
    {
        $this->add($name, 'Search.Callback', $config);

        return $this;
    }

    /**
     * compare method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function compare($name, array $config = [])
    {
        $this->add($name, 'Search.Compare', $config);

        return $this;
    }

    /**
     * custom method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function custom($name, array $config = [])
    {
        $this->add($name, $config['className'], $config);

        return $this;
    }

    /**
     * Loads a search filter.
     *
     * @param string $name Name of the field
     * @param string $filter Filter name
     * @param array $options Filter options.
     * @return \Search\Model\Filter\Base
     * @throws \InvalidArgumentException When no filter was found.
     */
    public function loadFilter($name, $filter, array $options = [])
    {
        if (empty($options['className'])) {
            $class = Inflector::classify($filter);
        } else {
            $class = $options['className'];
            unset($options['className']);
        }
        $className = App::className($class, 'Model\Filter');
        if (!$className) {
            throw new InvalidArgumentException(sprintf('Search filter "%s" was not found.', $class));
        }

        return new $className($name, $this, $options);
    }

    /**
     * Magic method to add filters using custom types.
     *
     * @param string $method Method name.
     * @param array $args Arguments.
     * @return $this
     */
    public function __call($method, $args)
    {
        if (!isset($args[1])) {
            $args[1] = [];
        }

        return $this->add($args[0], $method, $args[1]);
    }
}
