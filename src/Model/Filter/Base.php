<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\QueryInterface;
use Search\Manager;
use UnexpectedValueException;

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
     * @var \Cake\Datasource\QueryInterface
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
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name, Manager $manager, array $config = [])
    {
        $this->_manager = $manager;

        $config += [
            'fields' => $name,
            'aliasField' => true,
            'name' => $name,
            'alwaysRun' => false,
            'filterEmpty' => false,
            'defaultValue' => null,
            'multiValue' => false,
            'multiValueSeparator' => null,
            'flatten' => true,
            'beforeProcess' => null,
        ];

        if (isset($config['field'])) {
            throw new \InvalidArgumentException(
                'The `field` option has been renamed to `fields`.'
            );
        }
        $config['fields'] = (array)$config['fields'];
        $this->setConfig($config);

        if (
            empty($config['fields']) ||
            (!array_filter($config['fields'], function ($value) {
                return strlen($value) > 0;
            }))
        ) {
            throw new \InvalidArgumentException(
                'The `field` option is invalid. Expected a non-empty string or array.'
            );
        }

        if (
            !is_string($config['name']) ||
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
    public function manager(): Manager
    {
        return $this->_manager;
    }

    /**
     * Get the database field name(s) as an array.
     *
     * @return array
     */
    public function fields(): array
    {
        $field = $this->getConfig('fields');
        if (!$this->getConfig('aliasField')) {
            return $field;
        }

        $repository = $this->manager()->getRepository();
        if (!method_exists($repository, 'aliasField')) {
            return $field;
        }

        $return = [];
        foreach ($field as $fld) {
            $return[] = $repository->aliasField($fld);
        }

        return $return;
    }

    /**
     * Get the field name from HTTP GET query string.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->getConfig('name');
    }

    /**
     * Check if the name is present in the arguments from HTTP GET.
     *
     * @return bool
     */
    public function present(): bool
    {
        return $this->getConfig('alwaysRun') || array_key_exists($this->name(), $this->_args);
    }

    /**
     * Check if empty value for name in query string should be filtered out.
     *
     * @return bool
     */
    public function filterEmpty(): bool
    {
        return $this->getConfig('filterEmpty');
    }

    /**
     * Checks whether this finder should be skipped.
     *
     * @return bool
     */
    public function skip(): bool
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

        $passedValue = $this->passedValue();
        if ($passedValue === null) {
            return $value;
        }

        return $passedValue;
    }

    /**
     * @return string|array|null
     */
    protected function passedValue()
    {
        if (!isset($this->_args[$this->name()])) {
            return null;
        }

        $value = $this->_args[$this->name()];

        if (is_array($value)) {
            return $this->getConfig('multiValue') ? $value : null;
        }

        if ($this->getConfig('multiValueSeparator')) {
            return explode($this->getConfig('multiValueSeparator'), $value);
        }

        return $value;
    }

    /**
     * Sets the args.
     *
     * @param array $args Value.
     *
     * @return $this
     */
    public function setArgs(array $args)
    {
        $this->_args = $args;

        return $this;
    }

    /**
     * Gets the args.
     *
     * @return array
     */
    public function getArgs(): array
    {
        return $this->_args;
    }

    /**
     * Sets the query object.
     *
     * @param \Cake\Datasource\QueryInterface $query Query instance.
     * @return $this
     */
    public function setQuery(QueryInterface $query)
    {
        $this->_query = $query;

        return $this;
    }

    /**
     * Gets the query object.
     *
     * @return \Cake\Datasource\QueryInterface
     */
    public function getQuery(): QueryInterface
    {
        return $this->_query;
    }

    /**
     * Run the filter.
     *
     * @param \Cake\Datasource\QueryInterface $query Query instance.
     * @param array $args Filter arguments.
     * @return bool True if processed, false if skipped
     */
    public function __invoke(QueryInterface $query, array $args): bool
    {
        $this->setQuery($query)->setArgs($args);

        if ($this->skip()) {
            return false;
        }

        $beforeProcess = $this->getConfig('beforeProcess');
        if ($beforeProcess === null) {
            return $this->process();
        }

        if (!is_callable($beforeProcess)) {
            throw new UnexpectedValueException('Value for "beforeProcess" config must be a valid callable');
        }

        $return = $beforeProcess($this->getQuery(), $this->getArgs(), $this);
        if ($return === false) {
            return false;
        }
        if (is_array($return)) {
            $this->setArgs($return);
        }

        return $this->process();
    }

    /**
     * Modify the actual query object and append conditions based on the
     * subclass business rules and type.
     *
     * @return bool True if processed, false if skipped
     */
    abstract public function process(): bool;
}
