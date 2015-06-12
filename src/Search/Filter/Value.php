<?php
namespace Burzum\Search\Search\Filter;

class Value extends Base
{

    /**
     * Process a value condition ($x == $y).
     *
     * @return void
     */
    public function process()
    {
        if (!$this->present()) {
            return;
        }

        $this->query()->andWhere(function($e) {
            return $e->in($this->field(), $this->value());
        });

    }
}
