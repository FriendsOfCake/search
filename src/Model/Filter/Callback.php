<?php
namespace Search\Model\Filter;

class Callback extends Base
{

    /**
     * Modify query using callback.
     *
     * @return bool
     */
    public function process()
    {
        call_user_func($this->getConfig('callback'), $this->getQuery(), $this->getArgs(), $this);

        return true;
    }
}
