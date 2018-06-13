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
     * Adds a filter
     *
     * @param  \Search\Model\Filter\FilterInterface $filter Filter
     * @return $this
     */
    public function add(FilterInterface $filter);

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
     * @param  string|\Search\Model\Filter\FilterInterface
     * @return bool
     */
    public function has($name);
}
