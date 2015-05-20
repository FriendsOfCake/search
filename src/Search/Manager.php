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

    /**
     * Config
     *
     * @var array
     */
    protected $_config = [];

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
     * Return all config
     *
     * @return array Config
     */
    public function all()
    {
        return $this->_config;
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
        if (isset($config['className'])) {
            $this->_config[$name] = new $config['className']($name, $config, $this);
            return $this;
        }
        if (class_exists('\FOC\Search\Search\Type\\' . $name)) {
            $this->_config[$name] = 'Type\\' . $name;
            return $this;
        }
        if (class_exists('\\App\\Search\\Type\\' . $name)) {
            $this->_config[$name] = '\\App\\Search\\Type\\' . $name;
            return $this;
        }
    }

    public function __call($method, $args)
    {
        $class = '\FOC\Search\Search\Type\\' . Inflector::classify($method);
        if (class_exists($class)) {
            $this->_config[$args[0]] = new $class($args[0], $args[1], $this);
            return $this;
        }
    }
}
