<?php
namespace Search\Model\Filter;

trait FilterMethodsTrait
{
    /**
     * Boolean method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function boolean($name, array $config = [])
    {
        $this->add($name, 'Search.Boolean', $config);

        return $this;
    }

    /**
     * Exists method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function exists($name, array $config = [])
    {
        $this->add($name, 'Search.Exists', $config);

        return $this;
    }

    /**
     * Like method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function like($name, array $config = [])
    {
        $this->add($name, 'Search.Like', $config);

        return $this;
    }

    /**
     * Value method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function value($name, array $config = [])
    {
        $this->add($name, 'Search.Value', $config);

        return $this;
    }

    /**
     * Finder method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function finder($name, array $config = [])
    {
        $this->add($name, 'Search.Finder', $config);

        return $this;
    }

    /**
     * Callback method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function callback($name, array $config = [])
    {
        $this->add($name, 'Search.Callback', $config);

        return $this;
    }

    /**
     * Compare method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function compare($name, array $config = [])
    {
        $this->add($name, 'Search.Compare', $config);

        return $this;
    }

    /**
     * Custom method
     *
     * @param string $name Name
     * @param array $config Config
     * @return $this
     */
    public function custom($name, array $config = [])
    {
        $this->add($name, $config['className'], $config);

        return $this;
    }

    /**
     * Magic method to add filters using custom types.
     *
     * @param string $method Method name.
     * @param array $args Arguments.
     * @return $this
     */
    public function __call($method, $args)
    {
        if (!isset($args[1])) {
            $args[1] = [];
        }

        return $this->add($args[0], $method, $args[1]);
    }
}
