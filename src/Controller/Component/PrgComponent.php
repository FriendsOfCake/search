<?php
namespace Search\Controller\Component;

use Cake\Controller\Component;

class PrgComponent extends Component
{

    /**
     * Default config
     *
     * ### Options
     * - `actions` Method name(s) of actions to use PRG. Or bool for all or none.
     *   You can pass a single action as string or multiple as array. Default is
     *   true and all actions will be processed by the component.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'actions' => true,
    ];

    /**
     * Checks if the current request has posted data and redirects the users
     * to the same action after converting the post data into GET params
     *
     * @return void|\Cake\Network\Response
     */
    public function startup()
    {
        $this->controller = $this->_registry->getController();
        $this->request = $this->controller->request;

        if ($this->_actionCheck()) {
            return $this->conversion();
        }
    }

    /**
     * POST to GET / GET to POST conversion
     *
     * @param bool $redirect Redirect on post, default true.
     * @return \Cake\Network\Response|null
     */
    public function conversion($redirect = true)
    {
        if (!$this->request->is('post')) {
            $this->request->data = $this->controller->request->query;
            return null;
        }
        if ($redirect) {
            return $this->controller->redirect($this->request->params['pass'] + ['?' => $this->request->data]);
        }
        return null;
    }

    /**
     * Checks if the action should be processed by the component.
     *
     * @return bool
     */
    protected function _actionCheck()
    {
        $actions = $this->config('actions');
        if (is_bool($actions)) {
            return $actions;
        }
        if (is_string($actions)) {
            $actions = [$actions];
        }
        if (in_array($this->request->action, $actions)) {
            return true;
        }
        return false;
    }
}
