<?php
namespace Search\ORM;

use Cake\Core\InstanceConfigTrait;

class Search {

	protected $_config = [];

	public function all() {
		return $this->_config;
	}

	public function like($name, $config = []) {
		$this->_config[$name] = new Search\Like($name, $config);
		return $this;
	}

	public function value($name, $config = []) {
		$this->_config[$name] = new Search\Value($name, $config);
		return $this;
	}

	public function finder($name, $config = []) {
		$this->_config[$name] = new Search\Finder($name, $config);
		return $this;
	}

	public function callback($name, $config = []) {
		$this->_config[$name] = new Search\Callback($name, $config);
		return $this;
	}

}
