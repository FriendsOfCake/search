<?php
namespace Search\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;

class PrgComponent extends Component {

	public $presetVar = true;

/**
 * Initialize properties.
 *
 * @param array $config The config data.
 * @return void
 */
	public function initialize(array $config) {
		$controller = $this->_registry->getController();
		$request = $controller->request;

		if (!$request->is('post')) {
			return;
		}

		$passArgs = '';
		if (isset($request->params['pass']) && !empty($request->params['pass'])) {
			$passArgs = implode('/', $request->params['pass']);
		}

		$controller->redirect([$passArgs, '?' => $request->data]);
	}

}
