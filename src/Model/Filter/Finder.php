<?php
namespace Search\Model\Filter;

class Finder extends Base
{

    /**
     * Returns the finder method to use.
     *
     * @return string
     */
    public function finder()
    {
        $finder = $this->config('finder');

        return $finder ?: $this->name();
    }

    /**
     * Process a value condition ($x == $y).
     *
     * @return void
     */
    public function process()
    {
        if ($this->skip()) {
            return;
        }

        $this->query()->find($this->finder(), (array)$this->args());
    }
}
