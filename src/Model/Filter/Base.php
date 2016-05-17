<?php
namespace Search\Model\Filter;

use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Query;
use Search\Manager;

/**
 * Base class for search type classes.
 *
 */
abstract class Base
{

    use InstanceConfigTrait;

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * The parent Search Manager.
     *
     * @var \Search\Manager
     */
    protected $_manager;

    /**
     * Set the data to process on.
     *
     * @var array
     */
    protected $_args = [];

    /**
     * Query object.
     *
     * @var \Cake\ORM\Query
     */
    protected $_query;

    /**
     * Constructor.
     *
     * By default the name of the HTTP GET query argument will be assumed
     * the field name in the database as well.
     *
     * @param string $name Name.
     * @param \Search\Manager $manager Manager.
     * @param array $config Config.
     */
    public function __construct($name, Manager $manager, array $config = [])
    {
        $this->_manager = $manager;

        $defaults = [
            'field' => $name,
            'name' => $name,
            'validate' => [],
            'alwaysRun' => false,
            'filterEmpty' => false,
            'defaultValue' => null
        ];

        $this->config(array_merge($defaults, $config));
    }

    /**
     * Get the manager.
     *
     * @return \Search\Manager
     */
    public function manager()
    {
        return $this->_manager;
    }

    /**
     * Get the database field name.
     *
     * @return string|array
     */
    public function field()
    {
        return $this->config('field');
    }

    /**
     * Get the database field name(s) as an array.
     *
     * @return array
     */
    public function fields()
    {
        return (array)$this->config('field');
    }

    /**
     * Get the field name from HTTP GET query string.
     *
     * @return string
     */
    public function name()
    {
        return $this->config('name');
    }

    /**
     * Check if the name is present in the arguments from HTTP GET.
     *
     * @return bool
     */
    public function present()
    {
        return $this->config('alwaysRun') || array_key_exists($this->name(), $this->_args);
    }

    /**
     * Check if empty value for name in query string should be filtered out.
     *
     * @return bool
     */
    public function filterEmpty()
    {
        return $this->config('filterEmpty');
    }

    /**
     * Checks whether this finder should be skipped.
     *
     * @return bool
     */
    public function skip()
    {
        return !$this->present() ||
            ($this->filterEmpty() &&
                empty($this->_args[$this->name()]) &&
                !is_numeric($this->_args[$this->name()])
            );
    }

    /**
     * Get the value of the "name" from HTTP GET arguments.
     *
     * @return mixed
     */
    public function value()
    {
        return isset($this->_args[$this->name()]) ? $this->_args[$this->name()] : $this->_config['defaultValue'];
    }

    /**
     * Get / Set the args.
     *
     * @param array $value Value.
     * @return void|array
     */
    public function args(array $value = null)
    {
        if ($value === null) {
            return $this->_args;
        }

        $this->_args = $value;
    }

    /**
     * Get / Set the validation rules.
     *
     * @param array $value Value.
     * @return void|array
     */
    public function validate(array $value = null)
    {
        if ($value === null) {
            return $this->config('validate');
        }

        $this->config('validate', $value);
    }

    /**
     * Valid method.
     *
     * @return bool
     */
    public function valid()
    {
        $rules = $this->validate();
        if (empty($rules)) {
            return true;
        }
    }

    /**
     * Get / Set the query object.
     *
     * @param \Cake\ORM\Query $value Value.
     * @return void|\Cake\ORM\Query
     */
    public function query(Query $value = null)
    {
        if ($value === null) {
            return $this->_query;
        }

        $this->_query = $value;
    }

    /**
     * Modify the actual query object and append conditions based on the
     * subclass business rules and type.
     *
     * @return void
     */
    abstract public function process();
}
