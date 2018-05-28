<?php
namespace Search\Model\Filter;

use Cake\Core\App;
use Cake\Utility\Inflector;
use InvalidArgumentException;
use Search\Manager;

/**
 * Filter Locator
 */
class FilterLocator
{
    /**
     * Manager
     *
     * @var \Search\Manager $manager Manager
     */
    protected $manager;

    /**
     * Constructor
     *
     * @param \Search\Manager $manager Manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Loads a search filter.
     *
     * @param string $name Name of the field
     * @param string $filter Filter name
     * @param array $options Filter options.
     * @return \Search\Model\Filter\Base
     * @throws \InvalidArgumentException When no filter was found.
     */
    public function locate($name, $filter, array $options = [])
    {
        if (empty($options['className'])) {
            $class = Inflector::classify($filter);
        } else {
            $class = $options['className'];
            unset($options['className']);
        }

        $className = App::className($class, 'Model\Filter');
        if (!$className) {
            throw new InvalidArgumentException(sprintf('Search filter "%s" was not found.', $class));
        }

        return new $className($name, $this->manager, $options);
    }
}
