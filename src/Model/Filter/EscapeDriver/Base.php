<?php
namespace Search\Model\Filter\EscapeDriver;

use Cake\Core\InstanceConfigTrait;

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
     * {@inheritDoc}
     *
     * @param array $config Config.
     */
    public function __construct(array $config = [])
    {
        $this->config($config);
    }

    /**
     * Replace substitutions with original wildcards
     * but first, escape the original wildcards in the text to use them as normal search text
     *
     * @param string $value Value.
     * @return string Value
     */
    abstract public function formatWildcards($value);
}
