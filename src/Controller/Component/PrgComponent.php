<?php
namespace Search\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;

class PrgComponent extends Component
{
    /**
     * $_defaultConfig For the Component.
     *
     * ### Options
     * - `stripParams` Fields to strip from the data to query param conversion. Strips the `__csrfToken` by default.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'stripParams' => ['_csrfToken'],
    ];

    /**
     * Checks if the current request has posted data and redirects the users
     * to the same action after converting the post data into GET params
     *
     * @return void|\Cake\Network\Response
     */
    public function startup()
    {
        $controller = $this->_registry->getController();
        $request = $controller->request;

        if (!$request->is('post')) {
            $request->data = $request->query;
            return;
        }

        $data = $request->data;
        foreach ((array)$this->config('stripParams') as $param) {
            unset($data[$param]);
        }
        return $controller->redirect($request->params['pass'] + ['?' => $data]);
    }
}
