<?php
namespace Search\Controller\Component;

use Cake\Controller\Component;
use Cake\Utility\Hash;

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
     * @return \Cake\Network\Response|null
     */
    public function startup()
    {
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
            $this->request->data = $this->request->query;
            return null;
        }
        if (!$redirect) {
            return null;
        }

        list($url) = explode('?', $this->request->here(false));

        $params = Hash::filter($this->request->data);
        if ($params) {
            $url .= '?' . http_build_query($params);
        }

        return $this->_registry->getController()->redirect($url);
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
        return in_array($this->request->action, $actions);
    }
}
