<?php
namespace Search\ORM\Search;

use Cake\ORM\Query;
use Cake\Core\InstanceConfigTrait;

abstract class Base {

	use InstanceConfigTrait;

/**
 * Default configuration
 *
 * @var array
 */
	protected $_defaultConfig = [];

	public function __construct($name, array $config = []) {
		$this->config(array_merge(['field' => $name, 'name' => $name], $config));
	}

	public function field() {
		return $this->config('field');
	}

	public function name() {
		return $this->config('name');
	}

	public function present($args) {
		return array_key_exists($this->name(), $args);
	}

	public function value($args) {
		return $args[$this->name()];
	}

	abstract public function process(Query $query, array $args);

}
