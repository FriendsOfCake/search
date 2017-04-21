<?php
namespace Search\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Utility\Hash;

class PrgComponent extends Component
{

    /**
     * Default config
     *
     * ### Options
     * - `actions` : Action name(s) to use PRG for. You can pass a single action
     *   as string or multiple as array. If boolean `true` all actions will be
     *   processed if `false` none. Default is ['index', 'lookup'].
     * - `queryStringToData` : Set query string as request data. Default `true`.
     * - `queryStringWhitelist` : An array of whitelisted query strings to be kept.
     *   Defaults to the Paginator `'sort'`, `'direction'` and `'limit'` ones.
     * - `emptyValues` : A map of fields and their values to be considered empty 
     *   (will not be passed along in the URL).
     *
     * @var array
     */
    protected $_defaultConfig = [
        'actions' => ['index', 'lookup'],
        'queryStringToData' => true,
        'queryStringWhitelist' => ['sort', 'direction', 'limit'],
        'emptyValues' => [],
    ];

    /**
     * Checks if the current request has posted data and redirects the users
     * to the same action after converting the post data into GET params
     *
     * @return \Cake\Network\Response|null
     */
    public function startup()
    {
        if (!$this->_actionCheck()) {
            return null;
        }

        return $this->conversion();
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
            if ($this->config('queryStringToData')) {
                $this->request->data = $this->request->query;
            }

            return null;
        }

        if (!$redirect) {
            return null;
        }

        list($url) = explode('?', $this->request->here(false));

        $params = $this->_filterParams();
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

    /**
     * Filters the params from POST data and merges in the whitelisted query string ones.
     *
     * @return array
     */
    protected function _filterParams()
    {
        $params = Hash::filter($this->request->data);

        foreach ((array)$this->config('emptyValues') as $field => $value) {
            if (!isset($params[$field])) {
                continue;
            }

            if ($params[$field] === (string)$value) {
                unset($params[$field]);
            }
        }

        if (!$this->config('queryStringWhitelist')) {
            return $params;
        }

        foreach ($this->config('queryStringWhitelist') as $field) {
            if (!isset($params[$field]) && $this->request->query($field) !== null) {
                $params[$field] = $this->request->query($field);
            }
        }

        return $params;
    }
}
