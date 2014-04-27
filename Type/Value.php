<?php
namespace Search\Type;

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

		$query->andWhere([$this->field() => $this->value($args)]);
	}

}
