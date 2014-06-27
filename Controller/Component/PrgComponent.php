<?php
namespace Search\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;

class PrgComponent extends Component {

	public $presetVar = true;

	public function initialize(Event $event) {
		$controller = $event->subject;
		$request = $controller->request;

		if (!$request->is('post')) {
			return;
		}

		$passArgs = '';
		if (isset($request->params['pass']) && !empty($request->params['pass'])):
			$passArgs = implode('/', $request->params['pass']);
		endif;

		$controller->redirect([$passArgs, '?' => $request->data]);
	}

}
