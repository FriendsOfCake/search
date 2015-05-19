<?php
namespace Search\Search;

use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Table;

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
     * like method
     *
     * @param string $name Name
     * @param array $config Config
     * @return Manager Instance
     */
    public function like($name, $config = [])
    {
        $this->_config[$name] = new Type\Like($name, $config, $this);
        return $this;
    }

    /**
     * value method
     *
     * @param string $name Name
     * @param array $config Config
     * @return Manager Instance
     */
    public function value($name, $config = [])
    {
        $this->_config[$name] = new Type\Value($name, $config, $this);
        return $this;
    }

    /**
     * finder method
     *
     * @param string $name Name
     * @param array $config Config
     * @return Manager Instance
     */
    public function finder($name, $config = [])
    {
        $this->_config[$name] = new Type\Finder($name, $config, $this);
        return $this;
    }

    /**
     * callback method
     *
     * @param string $name Name
     * @param array $config Config
     * @return Manager Instance
     */
    public function callback($name, $config = [])
    {
        $this->_config[$name] = new Type\Callback($name, $config, $this);
        return $this;
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
        if (class_exists('Type\\' . $name)) {
            $this->_config[$name] = 'Type\\' . $name;
            return $this;
        }
        if (class_exists('\\App\\Search\\Type\\' . $name)) {
            $this->_config[$name] = '\\App\\Search\\Type\\' . $name;
            return $this;
        }
    }
}
