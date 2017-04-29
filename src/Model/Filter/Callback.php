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
        call_user_func($this->config('callback'), $this->getQuery(), $this->getArgs(), $this);

        return true;
    }
}
