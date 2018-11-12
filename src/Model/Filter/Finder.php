<?php
namespace Search\Model\Filter;

class Finder extends Base
{
    /**
     * @var array
     */
    protected $_defaultConfig = [
        'map' => [],
        'options' => [],
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
        $args = $this->getArgs();
        $map = $this->getConfig('map');
        foreach ($map as $to => $from) {
            $args[$to] = isset($args[$from]) ? $args[$from] : null;
        }

        $options = $this->getConfig('options');
        $args += $options;

        $this->getQuery()->find($this->finder(), $args);

        return true;
    }
}
