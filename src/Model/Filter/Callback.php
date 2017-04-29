<?php
namespace Search\Model\Filter;

class Callback extends Base
{

    /**
     * Modify query using callback.
     *
     * @return void
     */
    public function process()
    {
        if ($this->skip()) {
            return;
        }

        call_user_func($this->config('callback'), $this->getQuery(), $this->getArgs(), $this);
    }
}
