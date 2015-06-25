<?php
namespace Search;

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
     * @param \Cake\ORM\Table $table Table
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
     * @return \Cake\ORM\Table Table Instance
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
     * @return $this
     */
    public function like($name, array $config = [])
    {
        $this->_config[$name] = new Type\Like($name, $this, $config);
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
        $this->_config[$name] = new Type\Value($name, $this, $config);
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
        $this->_config[$name] = new Type\Finder($name, $this, $config);
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
        $this->_config[$name] = new Type\Callback($name, $this, $config);
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
        $this->_config[$name] = new Type\Compare($name, $this, $config);
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
        $this->_config[$name] = new $config['className']($name, $this, $config);
        return $this;
    }
}
