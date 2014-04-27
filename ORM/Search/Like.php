<?php
namespace Search\ORM\Search;

use Cake\ORM\Query;

class Like extends Base {

/**
 * Default configuration
 *
 * @var array
 */
	protected $_defaultConfig = [
		'before' => false,
		'after' => false
	];

/**
 * Process a LIKE condition ($x LIKE $y)
 *
 * @param  Query  $query
 * @param  array $args
 * @return void
 */
	public function process(Query $query, array $args) {
		if (!$this->present($args)) {
			return;
		}

		$left = $this->field() . ' LIKE';
		$right = $this->_wildCards($this->value($args));

		$query->where([$left => $right]);
	}

/**
 * Wrap wild cards around the value
 *
 * @param  string $value
 * @return string
 */
	protected function _wildCards($value) {
		if ($this->config('before')) {
			$value = '%' . $value;
		}

		if ($this->config('after')) {
			$value = $value . '%';
		}

		return $value;
	}

}
