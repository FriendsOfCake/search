<?php
namespace Search;

use Cake\Core\InstanceConfigTrait;
use Cake\ORM\Table;

class Manager {

	protected $_table;

	protected $_config = [];

	public function __construct(Table $table) {
		$this->_table = $table;
	}

	public function all() {
		return $this->_config;
	}

	public function table() {
		return $this->_table;
	}

	public function like($name, $config = []) {
		$this->_config[$name] = new Type\Like($name, $config, $this);
		return $this;
	}

	public function value($name, $config = []) {
		$this->_config[$name] = new Type\Value($name, $config, $this);
		return $this;
	}

	public function finder($name, $config = []) {
		$this->_config[$name] = new Type\Finder($name, $config, $this);
		return $this;
	}

	public function callback($name, $config = []) {
		$this->_config[$name] = new Type\Callback($name, $config, $this);
		return $this;
	}

}
