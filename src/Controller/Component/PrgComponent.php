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
     * - `actions` : Action name(s) to use PRG for. You can pass a single action
     *   as string or multiple as array. If boolean `true` all actions will be
     *   processsed if `false` none. Default is ['index'].
     *
     * @var array
     */
    protected $_defaultConfig = [
        'actions' => ['index']
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
        return in_array($this->request->action, (array)$actions, true);
    }
}
