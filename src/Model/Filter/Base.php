<?php
namespace Search\Model\Filter;

use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\QueryInterface;
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
     * @var \Cake\Datasource\QueryInterface|null
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
            'aliasField' => true,
            'name' => $name,
            'validate' => [],
            'alwaysRun' => false,
            'filterEmpty' => false,
            'defaultValue' => null,
            'multiValue' => false,
            'flatten' => true,
        ];
        $config += $defaults;
        $this->config($config);

        if ((empty($config['field']) && $config['field'] !== '0') ||
            (is_array($config['field']) && !array_filter($config['field'], 'strlen'))
        ) {
            throw new \InvalidArgumentException(
                'The `field` option is invalid. Expected a non-empty string or array.'
            );
        }

        if (!is_string($config['name']) ||
            (empty($config['name']) && $config['name'] !== '0')
        ) {
            throw new \InvalidArgumentException(
                'The `$name` argument is invalid. Expected a non-empty string.'
            );
        }
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
        $field = $this->config('field');
        if (!$this->config('aliasField')) {
            return $field;
        }

        $repository = $this->manager()->repository();
        if (!method_exists($repository, 'aliasField')) {
            return $field;
        }

        if (is_string($field)) {
            return $repository->aliasField($field);
        }

        $return = [];
        foreach ($field as $fld) {
            $return[] = $repository->aliasField($fld);
        }

        return $return;
    }

    /**
     * Get the database field name(s) as an array.
     *
     * @return array
     */
    public function fields()
    {
        return (array)$this->field();
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
        $value = $this->_config['defaultValue'];
        if (isset($this->_args[$this->name()])) {
            $passedValue = $this->_args[$this->name()];
            if (!is_array($passedValue) ||
                $this->config('multiValue')
            ) {
                return $passedValue;
            }
        }

        return $value;
    }

    /**
     * Sets the args.
     *
     * @param array $args Value.
     *
     * @return void
     */
    public function setArgs(array $args)
    {
        $this->_args = $args;
    }

    /**
     * Gets the args.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->_args;
    }

    /**
     * Get / Set the args.
     *
     * @deprecated 3.0.0 Use setArgs()/getArgs() instead.
     * @param array|null $value Value.
     * @return array|null
     */
    public function args(array $value = null)
    {
        if ($value === null) {
            return $this->getArgs();
        }

        $this->setArgs($value);
    }

    /**
     * Get / Set the validation rules.
     *
     * @param array|null $value Value.
     * @return array|null
     * @codeCoverageIgnore
     * @internal
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
     * @codeCoverageIgnore
     * @internal
     */
    public function valid()
    {
        $rules = $this->validate();
        if (empty($rules)) {
            return true;
        }

        return false;
    }

    /**
     * Sets the query object.
     *
     * @param \Cake\Datasource\QueryInterface $value Value.
     * @return void
     */
    public function setQuery(QueryInterface $value)
    {
        $this->_query = $value;
    }

    /**
     * Gets the query object.
     *
     * @return \Cake\Datasource\QueryInterface|null
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Get / Set the query object.
     *
     * @deprecated 3.0.0 Use setQuery()/getQuery() instead.
     * @param \Cake\Datasource\QueryInterface|null $value Value.
     * @return \Cake\Datasource\QueryInterface|null
     */
    public function query(QueryInterface $value = null)
    {
        if ($value === null) {
            return $this->getQuery();
        }

        $this->setQuery($value);
    }

    /**
     * Modify the actual query object and append conditions based on the
     * subclass business rules and type.
     *
     * @return bool True if processed, false if skipped
     */
    abstract public function process();
}
