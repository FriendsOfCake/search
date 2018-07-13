<?php
namespace Search\Model\Filter;

use ArrayIterator;
use Search\Model\Filter\FilterLocatorInterface;

/**
 * FilterCollection
 */
class FilterCollection implements FilterCollectionInterface
{
    /**
     * @var array List of filter objects
     */
    protected $_filters = [];

    /**
     * Filter Locator
     *
     * @var \Search\Model\Filter\FilterLocatorInterface
     */
    protected $_filterLocator;

    /**
     * Constructor
     *
     * @param \Search\Model\Filter\FilterLocatorInterface $locator Filter locator
     */
    public function __construct(FilterLocatorInterface $locator)
    {
        $this->_filterLocator = $locator;

        $this->initialize();
    }

    /**
     * Initialize method.
     *
     * @return void
     */
    public function initialize()
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
    public function add($name, $filter, array $options = [])
    {
        $this->_filters[$name] = $this->loadFilter($name, $filter, $options);

        return $this;
    }

    /**
     * Loads a search filter.
     *
     * @param string $name Filter name.
     * @param string $filter Filter class name in short form like "Search.Value" or FQCN.
     * @param array $options Filter options.
     * @return \Search\Model\Filter\FilterInterface
     * @throws \InvalidArgumentException When no filter was found.
     */
    public function loadFilter($name, $filter, array $options = [])
    {
        return $this->_filterLocator->get($name, $filter, $options);
    }

    /**
     * Checks if a filter is in the collection
     *
     * @param string|\Search\Model\Filter\FilterInterface $name Name of the filter
     * @return bool
     */
    public function has($name)
    {
        if ($name instanceof FilterInterface) {
            $name = $name->name();
        }

        return isset($this->_filters[$name]);
    }

    /**
     * Removes a filter by name
     *
     * @param string $name Name of the filter
     * @return $this
     */
    public function remove($name)
    {
        unset($this->_filters[$name]);

        return $this;
    }

    /**
     * Retrieve an external iterator
     *
     * @return \Iterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_filters);
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset Offset
     * @return bool true on success or false on failure. The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset Offset
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if ($this->has($offset)) {
            return $this->_filters[$offset];
        }

        return null;
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset Offset
     * @param mixed $value Value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->_filters[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset Offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Returns the collection as array, mostly to be backward compatible
     *
     * @internal Don't use it it's for backward compatibility here
     * @return array
     */
    public function toArray()
    {
        return $this->_filters;
    }
}
