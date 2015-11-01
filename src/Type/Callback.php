<?php
namespace Search\Type;

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

        call_user_func([$this->manager()->table(), $this->config('name')], $this->query(), $this->args(), $this);
    }
}
