<?php
namespace FOC\Search\Search;

use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Table;
use Cake\Utility\Inflector;

class Manager
{

    /**
     * Table
     *
     * @var Table Instance
     */
    protected $_table;

    protected $_filters = [];

    protected $_collection = 'default';

    /**
     * Config
     *
     * @var array
     */
    protected $_config = [
        'types' => [],
        'typeClasses' => []
    ];

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
        return $this->_filters['types'];
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
     * custom method
     *
     * @param string $name Name
     * @param array $config Config
     * @return Manager Instance
     */
    public function custom($name, $config = [])
    {
        list($plugin, $filterClass) = pluginSplit($name);
        if (!empty($plugin)) {
            $this->_config['types'][$name] = '\\' . $plugin . '\Search\Type\\' . $filterClass;
            return $this;
        }
        if (isset($config['typeClasses'][$name])) {
            $this->_config['types'][$name] = new $config['typeClasses'][$name]($name, $config, $this);
            return $this;
        }
        if (class_exists('\FOC\Search\Search\Type\\' . $name)) {
            $this->_config['types'][$name] = 'Type\\' . $name;
            return $this;
        }
        if (class_exists('\App\Search\Type\\' . $name)) {
            $this->_config['types'][$name] = '\App\Search\Type\\' . $name;
            return $this;
        }
        throw new \RuntimeException(sprintf('Can\'t find filter class "%s"!', $name));
    }

    public function __call($method, $args)
    {
        $class = '\FOC\Search\Search\Type\\' . Inflector::classify($method);
        if (class_exists($class)) {
            $this->_config[$args[0]] = new $class($args[0], $args[1], $this);
            return $this;
        }
        return $this->custom($method, $args[0]);
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
 * @param string $name
 * @param string $filter
 * @param array $options
 * @return $this
 */
    public function add($name, $filter, array $options = [])
    {
        $this->_filters[$this->_collection][$name] = $this->_loadFilter($filter, $options);
        return $this;
    }

/**
 * Loads a search filter instance.
 *
 * @param string $name Name of the filter class to load.
 * @param array $options Filter options.
 * @return \Search\Search\Type\Base
 * @throws \InvalidArgumentException When no filter was found.
 */
    public function _loadFilter($name, array $options = [])
    {
        list($plugin, $name) = pluginSplit($name);
        if (!empty($plugin)) {
            $className = '\\' . $plugin . '\Search\Type\\' . $name;
            if (class_exists($className)) {
                return new $className($name, $options, $this);
            }
        }
        if (isset($config['typeClasses'][$name])) {
            return new $config['typeClasses'][$name]($name, $options, $this);
        }
        if (class_exists('\FOC\Search\Search\Type\\' . $name)) {
            $className = '\FOC\Search\Search\Type\\' . $name;
            return new $className($name, $options, $this);
        }
        if (class_exists('\App\Search\Type\\' . $name)) {
            $className = '\App\Search\Type\\' . $name;
            return new $className($name, $options, $this);
        }
        throw new \InvalidArgumentException(sprintf('Can\'t find filter class %s!', $name));
    }
}
