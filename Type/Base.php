<?php
namespace Search\Type;

use Cake\ORM\Query;
use Cake\Core\InstanceConfigTrait;
use Search\Manager;

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
 * The parent Search Manager
 *
 * @var \Search\Manager
 */
	protected $_manager;

/**
 * Set the data to process on
 *
 * @var array
 */
	protected $_args = [];

/**
 * Query object
 *
 * @var \Cake\ORM\Query
 */
	protected $_query;

/**
 * Constructor
 *
 * By default the name of the HTTP GET query argument will be assumed
 * the field name in the database as well.
 *
 * @param string 	$name
 * @param array 	$config
 */
	public function __construct($name, array $config = [], Manager $manager) {
		$this->_manager = $manager;

		$defaults = [
			'field' => $name,
			'name' => $name,
			'validate' => []
		];

		$this->config(array_merge($defaults, $config));
	}

/**
 * Get the manager
 *
 * @return \Search\Manager
 */
	public function manager() {
		return $this->_manager;
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
 * @return boolean
 */
	public function present() {
		return array_key_exists($this->name(), $this->_args);
	}

/**
 * Get the value of the "name" from HTTP GET arguments
 *
 * @return mixed
 */
	public function value() {
		return $this->_args[$this->name()];
	}

/**
 * Get / Set the args
 *
 * @param  array $value
 * @return void
 */
	public function args(array $value = null) {
		if ($value === null) {
			return $this->_args;
		}

		$this->_args = $value;
	}

/**
 * Get / Set the validation rules
 *
 * @param  array $value
 * @return void
 */
	public function validate(array $value = null) {
		if ($value === null) {
			return $this->config('validate');
		}

		$this->config('validate', $value);
	}

	public function valid() {
		$rules = $this->validate();
		if (empty($rules)) {
			return true;
		}

	}

/**
 * Get / Set the query object
 *
 * @param  Query $value
 * @return void
 */
	public function query(Query $value = null) {
		if ($value === null) {
			return $this->_query;
		}

		$this->_query = $value;
	}

/**
 * Modify the actual query object and append conditions based on the
 * subclass business rules and type
 *
 * @param  Query  $query
 * @param  array  $args
 * @return void
 */
	abstract public function process();

}
