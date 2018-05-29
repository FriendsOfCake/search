<?php
namespace Search\Model\Filter;

/**
 * Filter Locator Interface
 */
interface FilterLocatorInterface
{
    /**
     * Loads a search filter.
     *
     * @param string $name Name of the field
     * @param string $filter Filter name
     * @param array $options Filter options.
     * @return \Search\Model\Filter\Base
     * @throws \InvalidArgumentException When no filter was found.
     */
    public function locate($name, $filter, array $options = []);
}
