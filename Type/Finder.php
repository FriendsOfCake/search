<?php
namespace Search\Type;

use Cake\ORM\Query;

class Finder extends Base {

/**
 * Process a value condition ($x == $y)
 *
 * @return void
 */
	public function process() {
		if (!$this->present()) {
			return;
		}

		$this->query()->find($this->name(), $this->args());
	}

}
