<?php
namespace Search\Model\Filter;

class Finder extends Base
{
    /**
     * @var array
     */
    protected $_defaultConfig = [
        'map' => [],
    ];

    /**
     * Returns the finder method to use.
     *
     * @return string
     */
    public function finder()
    {
        $finder = $this->getConfig('finder');

        return $finder ?: $this->name();
    }

    /**
     * Add a condition based on a custom finder.
     *
     * @return bool
     */
    public function process()
    {
        $args = (array)$this->getArgs();
        $map = $this->getConfig('map');
        foreach ($map as $from => $to) {
            $args[$to] = isset($args[$from]) ? $args[$from] : null;
        }

        $this->getQuery()->find($this->finder(), $args);

        return true;
    }
}
