<?php
namespace Search\Type;

use Cake\ORM\Query;
use Cake\Core\InstanceConfigTrait;

/**
 * Base class for search type classes
 *
  */
abstract class Base {

	use InstanceConfigTrait;

/**
 * Default configuration
 *
 * @var array
 */
	protected $_defaultConfig = [];

/**
 * Constructor
 *
 * By default the name of the HTTP GET query argument will be assumed
 * the field name in the database as well.
 *
 * @param string 	$name
 * @param array 	$config
 */
	public function __construct($name, array $config = []) {
		$defaults = [
			'field' => $name,
			'name' => $name
		];

		$this->config(array_merge($defaults, $config));
	}

/**
 * Get the database field name
 *
 * @return string|array
 */
	public function field() {
		return $this->config('field');
	}

/**
 * Get the database field name(s) as an array
 *
 * @return array
 */
	public function fields() {
		return (array)$this->config('field');
	}

/**
 * Get the field name from HTTP GET query string
 *
 * @return string
 */
	public function name() {
		return $this->config('name');
	}

/**
 * Check if the name is present in the arguments from HTTP GET
 *
 * @param  array $args
 * @return boolean
 */
	public function present(array $args) {
		return array_key_exists($this->name(), $args);
	}

/**
 * Get the value of the "name" from HTTP GET arguments
 *
 * @param  array $args
 * @return mixed
 */
	public function value($args) {
		return $args[$this->name()];
	}

/**
 * Modify the actual query object and append conditions based on the
 * subclass business rules and type
 *
 * @param  Query  $query
 * @param  array  $args
 * @return void
 */
	abstract public function process(Query $query, array $args);

}
