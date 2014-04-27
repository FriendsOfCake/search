<?php
namespace Search\ORM\Search;

use Cake\ORM\Query;

class Value extends Base {

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

		$query->where([$this->field() => $this->value($args)]);
	}

}
