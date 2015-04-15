<?php
namespace Search\Type;

use Cake\ORM\Query;
use Cake\Database\Expression\TupleComparison;

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
            // $field = $this->field();

            // if (is_array($field)) {
            //     return new TupleComparison($field, $this->value());
            // }

            return $e->eq($this->field(), $this->value());
        });

    }
}
