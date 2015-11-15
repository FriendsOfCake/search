<?php
namespace Search\Model\Filter;

use Cake\ORM\Query;

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

        call_user_func($this->config('callback'), $this->query(), $this->args(), $this);
    }
}
