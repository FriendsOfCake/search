<?php
namespace Search;

use Cake\Core\InstanceConfigTrait;

class Search {

	protected $_config = [];

	public function all() {
		return $this->_config;
	}

	public function like($name, $config = []) {
		$this->_config[$name] = new Type\Like($name, $config);
		return $this;
	}

	public function value($name, $config = []) {
		$this->_config[$name] = new Type\Value($name, $config);
		return $this;
	}

	public function finder($name, $config = []) {
		$this->_config[$name] = new Type\Finder($name, $config);
		return $this;
	}

	public function callback($name, $config = []) {
		$this->_config[$name] = new Type\Callback($name, $config);
		return $this;
	}

}
