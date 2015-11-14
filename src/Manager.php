<?php
namespace Search;

use Cake\ORM\Table;
use Cake\Utility\Inflector;
use Search\Type;

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
     * @param Table $table Table
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
     * @return Table Table Instance
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
     * @deprecated Use Manager::add() instead.
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function like($name, array $config = [])
    {
        $this->add($name, 'Like', $config);
        return $this;
    }

    /**
     * value method
     *
     * @deprecated Use Manager::add() instead.
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function value($name, array $config = [])
    {
        $this->add($name, 'Value', $config);
        return $this;
    }

    /**
     * finder method
     *
     * @deprecated Use Manager::add() instead.
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function finder($name, array $config = [])
    {
        $this->add($name, 'Finder', $config);
        return $this;
    }

    /**
     * callback method
     *
     * @deprecated Use Manager::add() instead.
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function callback($name, array $config = [])
    {
        $this->add($name, 'Callback', $config);
        return $this;
    }
    /**
     * compare method
     *
     * @deprecated Use Manager::add() instead.
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function compare($name, array $config = [])
    {
        $this->add($name, 'Compare', $config);
        return $this;
    }
    /**
     * custom method
     *
     * @deprecated Use Manager::add() instead.
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
     * @return \Search\Type\Base
     * @throws \InvalidArgumentException When no filter was found.
     */
    public function loadFilter($name, $filter, array $options = [])
    {
        list($plugin, $filter) = pluginSplit($filter);
        $filter = Inflector::classify($filter);
        if (!empty($plugin)) {
            $className = '\\' . $plugin . '\Search\Type\\' . $filter;
            if (class_exists($className)) {
                return new $className($name, $this, $options);
            }
        }
        $className = '\Search\Type\\' . $filter;
        if (class_exists($className)) {
            return new $className($name, $this, $options);
        }
        $className = '\App\Search\Type\\' . $filter;
        if (class_exists($className)) {
            return new $className($name, $this, $options);
        }
        throw new \InvalidArgumentException(sprintf('Can\'t find filter class for filter "%s"!', $filter));
    }

    /**
     * Provides backward compatibility.
     *
     * @param string $method Method name.
     * @param array $args Arguments.
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!isset($args[1])) {
            $args[1] = [];
        }
        return $this->add($args[0], $method, $args[1]);
    }
}
