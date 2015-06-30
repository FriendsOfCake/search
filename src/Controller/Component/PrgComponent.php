<?php
namespace Search\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;

class PrgComponent extends Component
{
    /**
     * Checks if the current request has posted data and redirects the users
     * to the same action after converting the post data into GET params
     *
     * @return void|Cake\Network\Response
     */
    public function startup()
    {
        $controller = $this->_registry->getController();
        $request = $controller->request;

        if (!$request->is('post')) {
            $request->data = $request->query;
            return;
        }

        return $controller->redirect($request->params['pass'] + ['?' => $request->data]);
    }
}
