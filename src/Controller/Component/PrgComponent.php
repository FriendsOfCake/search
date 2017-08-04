<?php
namespace Search\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Event\Event;
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
     * - `queryStringBlacklist` : An array of form fields that should not end up in the query.
     * - `emptyValues` : A map of fields and their values to be considered empty
     *   (will not be passed along in the URL).
     *
     * @var array
     */
    protected $_defaultConfig = [
        'actions' => ['index', 'lookup'],
        'queryStringToData' => true,
        'queryStringWhitelist' => ['sort', 'direction', 'limit'],
        'queryStringBlacklist' => ['_csrfToken', '_Token'],
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
        if (!$this->_actionCheck() || !$this->request->is('post')) {
            return null;
        }

        return $this->_prg();
    }

    /**
     * Handle Controller.beforeRender event.
     *
     * @param \Cake\Event\Event $event Controller.beforeRender event
     * @return void
     */
    public function beforeRender(Event $event)
    {
        if (!$this->request->is('post')) {
            $this->_queryStringToData();
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
            $this->_queryStringToData();

            return null;
        }

        if (!$redirect) {
            return null;
        }

        return $this->_prg();
    }

    /**
     * Converts query string to post data if `queryStringToData` config is true
     *
     * @return void
     */
    protected function _queryStringToData()
    {
        if (!$this->config('queryStringToData')) {
            return;
        }

        $this->request->data = $this->request->query;
    }

    /**
     * Redirects the users to the same action after converting the post data into GET params
     *
     * @return \Cake\Network\Response|null
     */
    protected function _prg()
    {
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
        foreach ((array)$this->config('queryStringBlacklist') as $field) {
            unset($params[$field]);
        }

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
