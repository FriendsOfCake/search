<?php
namespace Search\Model\Filter;

use ArrayAccess;
use IteratorAggregate;

/**
 * Filter Collection Interface
 */
interface FilterCollectionInterface extends IteratorAggregate, ArrayAccess
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
     * @param  string $name Name of the filter
     * @return $this
     */
    public function remove($name);

    /**
     * Checks if a filter is in the collection
     *
     * @param  string|\Search\Model\Filter\Base $name Name of the filter
     * @return bool
     */
    public function has($name);

    /**
     * Loads a search filter.
     *
     * @param string $name Filter name.
     * @param string $filter Filter class name in short form like "Search.Value" or FQCN.
     * @param array $options Filter options.
     * @return \Search\Model\Filter\Base
     * @throws \InvalidArgumentException When no filter was found.
     */
    public function loadFilter($name, $filter, array $options = []);
}
