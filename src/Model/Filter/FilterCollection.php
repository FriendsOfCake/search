<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use ArrayIterator;
use Cake\Core\App;
use InvalidArgumentException;
use Iterator;
use Search\Manager;

/**
 * FilterCollection
 */
class FilterCollection implements FilterCollectionInterface
{
    use FilterMethodsTrait;

    /**
     * @var array<string, \Search\Model\Filter\Base> List of filter objects
     */
    protected array $filters = [];

    /**
     * Search Manager
     *
     * @var \Search\Manager
     */
    protected Manager $manager;

    /**
     * Constructor
     *
     * @param \Search\Manager $manager Search Manager instance.
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;

        $this->initialize();
    }

    /**
     * Initialize method.
     *
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * Adds filter to the collection.
     *
     * @param string $name Filter name.
     * @param string $filter Filter class name in short form like "Search.Value" or FQCN.
     * @param array $options Filter options.
     * @return $this
     */
    public function add(string $name, string $filter, array $options = []): static
    {
        $this->filters[$name] = $this->loadFilter($name, $filter, $options);

        return $this;
    }

    /**
     * Loads a search filter.
     *
     * @param string $name Filter name.
     * @param string $filter Filter class name in short form like "Search.Value" or FQCN.
     * @param array $options Filter options.
     * @return \Search\Model\Filter\Base
     * @throws \InvalidArgumentException When no filter was found.
     */
    protected function loadFilter(string $name, string $filter, array $options = []): Base
    {
        if (empty($options['className'])) {
            $class = $filter;
        } else {
            $class = $options['className'];
            unset($options['className']);
        }

        /** @var class-string<\Search\Model\Filter\Base>|null $className */
        $className = App::className($class, 'Model/Filter');
        if ($className === null) {
            throw new InvalidArgumentException(sprintf('Search filter "%s" was not found.', $class));
        }

        return new $className($name, $this->manager, $options);
    }

    /**
     * Checks if a filter is in the collection
     *
     * @param string $name Name of the filter
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * Returns filter from the collection
     *
     * @param string $name Name of the filter
     * @return \Search\Model\Filter\Base|null
     */
    public function get(string $name): ?Base
    {
        return $this->offsetGet($name);
    }

    /**
     * Removes a filter by name
     *
     * @param string $name Name of the filter
     * @return $this
     */
    public function remove(string $name): static
    {
        $this->offsetUnset($name);

        return $this;
    }

    /**
     * Retrieve an external iterator
     *
     * @return \Iterator
     */
    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->filters);
    }

    /**
     * Check whether filter with given name exists.
     *
     * @param mixed $name The name to check for.
     * @return bool True on success or false on failure.
     */
    public function offsetExists(mixed $name): bool
    {
        return isset($this->filters[$name]);
    }

    /**
     * Name of filter to retrieve.
     *
     * @param mixed $name Name of filter to retrieve.
     * @return \Search\Model\Filter\Base|null Filter instance or null.
     */
    public function offsetGet(mixed $name): ?Base
    {
        if ($this->offsetExists($name)) {
            return $this->filters[$name];
        }

        return null;
    }

    /**
     * Set filter.
     *
     * @param string $name Filter name.
     * @param \Search\Model\Filter\Base $value Filter instance to set.
     * @return void
     */
    public function offsetSet(mixed $name, mixed $value): void
    {
        $this->filters[$name] = $value;
    }

    /**
     * Name of filter to unset.
     *
     * @param mixed $name Name of filter to unset.
     * @return void
     */
    public function offsetUnset(mixed $name): void
    {
        unset($this->filters[$name]);
    }
}
