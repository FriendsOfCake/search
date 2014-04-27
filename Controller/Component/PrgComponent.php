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

		$controller->redirect(['?' => $request->data]);
	}

}
