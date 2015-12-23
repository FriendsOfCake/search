<?php
namespace Search;

use Cake\Core\App;
use Cake\ORM\Table;
use Cake\Utility\Inflector;
use InvalidArgumentException;

class Manager
{

    /**
     * Table
     *
     * @var Table Instance
     */
    protected $_table;

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
     * @param \Cake\ORM\Table $table Table
     */
    public function __construct(Table $table)
    {
        $this->_table = $table;
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
     * Return Table
     *
     * @return \Cake\ORM\Table Table Instance
     */
    public function table()
    {
        return $this->_table;
    }

    /**
     * Gets all filters in a given collection.
     *
     * @param string $collection Name of the filter collection.
     * @return array Array of filter instances.
     */
    public function getFilters($collection = 'default')
    {
        return $this->_filters[$collection];
    }

    /**
     * Sets or gets the filter collection name.
     *
     * @param string $name Name of the active filter collection to set.
     * @return mixed Returns $this or the name of the active collection if no $name was provided.
     */
    public function collection($name = null)
    {
        if ($name === null) {
            return $this->_collection;
        }
        if (!isset($this->_filters[$name])) {
            $this->_filters[$name] = [];
        }
        $this->_collection = $name;
        return $this;
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
        if (!class_exists($className)) {
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
