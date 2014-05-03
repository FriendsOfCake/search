<?php
namespace Search\Type;

use Cake\ORM\Query;

class Callback extends Base {

/**
 * Process a value condition ($x == $y)
 *
 * @param  Query  $query
 * @param  array $args
 * @return void
 */
	public function process() {
		if (!$this->present()) {
			return;
		}

		call_user_func($this->config('callback'), $this->query(), $this->args(), $this);
	}

}
