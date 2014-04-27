<?php
namespace Search\ORM\Search;

use Cake\ORM\Query;

class Callback extends Base {

/**
 * Process a value condition ($x == $y)
 *
 * @param  Query  $query
 * @param  array $args
 * @return void
 */
	public function process(Query $query, array $args) {
		if (!$this->present($args)) {
			return;
		}

		call_user_func($this->config('callback'), $query, $args, $this);
	}

}
