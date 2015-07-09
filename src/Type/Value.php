<?php
namespace Search\Type;

use Cake\ORM\Query;

class Value extends Base
{

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

        $this->query()->andWhere(function ($e) {
            return $e->in($this->field(), $this->value());
        });
    }
}
