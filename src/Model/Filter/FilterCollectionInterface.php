<?php
namespace Search\Model\Filter;

use ArrayAccess;
use IteratorAggregate;

/**
 * Filter Collection Interface
 */
interface FilterCollectionInterface extends ArrayAccess, IteratorAggregate
{
    /**
     * Adds filter to the collection.
     *
     * @param string $name Filter name.
     * @param string $filter Filter class name in short form like "Search.Value" or FQCN.
     * @param array $options Filter options.
     * @return $this
     */
    public function add($name, $filter, array $options = []);

    /**
     * Removes a filter by name
     *
     * @param string $name Name of the filter
     * @return $this
     */
    public function remove($name);

    /**
     * Checks if a filter is in the collection
     *
     * @param string $name Name of the filter
     * @return bool
     */
    public function has($name);

    /**
     * Returns filter from the collection
     *
     * @param string $name Name of the filter
     * @return \Search\Model\Filter\Base|null
     */
    public function get($name);
}
