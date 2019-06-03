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
     *   processed if `false` none. Default is ['index', 'lookup'].
     * - `queryStringWhitelist` : An array of whitelisted query strings to be kept.
     *   Defaults to the Paginator `'sort'`, `'direction'` and `'limit'` ones.
     * - `queryStringBlacklist` : An array of form fields that should not end up in the query.
     * - `emptyValues` : A map of fields and their values to be considered empty
     *   (will not be passed along in the URL).
     * - `modelClass` : Configure the controller's modelClass to be used for the query, used to
     *   populate the _isSearch view variable to allow for a reset button, for example.
     *   Set to false to disable the auto-setting of the view variable.
     * - `events`: List of events this component listens to. You can disable an
     *   event by setting it to false.
     *   E.g. `'events' => ['Controller.beforeRender' => false]`
     *
     * @var array
     */
    protected $_defaultConfig = [
        'actions' => ['index', 'lookup'],
        'queryStringWhitelist' => ['sort', 'direction', 'limit'],
        'queryStringBlacklist' => ['_csrfToken', '_Token'],
        'emptyValues' => [],
        'modelClass' => null,
        'events' => [
            'Controller.startup' => 'startup',
            'Controller.beforeRender' => 'beforeRender',
        ],
    ];

    /**
     * Get the Controller callbacks this Component is interested in.
     *
     * @return array
     */
    public function implementedEvents()
    {
        return Hash::filter($this->getConfig('events'));
    }

    /**
     * Checks if the current request has posted data and redirects the users
     * to the same action after converting the post data into GET params
     *
     * @return \Cake\Http\Response|null
     */
    public function startup()
    {
        if (!$this->request->is('post') || !$this->_actionCheck()) {
            return null;
        }

        list($url) = explode('?', $this->request->getRequestTarget());

        $params = $this->_filterParams();
        if ($params) {
            $params = Hash::expand($params);
            $url .= '?' . http_build_query($params);
        }

        return $this->_registry->getController()->redirect($url);
    }

    /**
     * Populates the $_isSearch view variable based on the current request.
     *
     * You need to configure the modelClass config if you are not using the controller's
     * default modelClass property.
     *
     * @return \Cake\Http\Response|null
     */
    public function beforeRender()
    {
        if (!$this->_actionCheck()) {
            return null;
        }

        $controller = $this->_registry->getController();
        $modelClass = $this->getConfig('modelClass', $controller->modelClass);
        if (!$modelClass) {
            return null;
        }

        list (, $modelName) = pluginSplit($modelClass);
        if (!isset($controller->{$modelName})) {
            return null;
        }

        /* @var \Cake\ORM\Table|\Search\Model\Behavior\SearchBehavior $model */
        $model = $controller->{$modelName};
        if (!$model->behaviors()->has('Search')) {
            return null;
        }

        $controller->set('_isSearch', $model->isSearch());
        $controller->set('_searchParams', $model->searchParams());
    }

    /**
     * Checks if the action should be processed by the component.
     *
     * @return bool
     */
    protected function _actionCheck()
    {
        $actions = $this->getConfig('actions');
        if (is_bool($actions)) {
            return $actions;
        }

        return in_array($this->request->getParam('action'), (array)$actions, true);
    }

    /**
     * Filters the params from POST data and merges in the whitelisted query string ones.
     *
     * @return array
     */
    protected function _filterParams()
    {
        $params = Hash::filter((array)$this->request->getData());

        foreach ((array)$this->getConfig('queryStringBlacklist') as $field) {
            unset($params[$field]);
        }

        foreach ((array)$this->getConfig('emptyValues') as $field => $value) {
            if (!isset($params[$field])) {
                continue;
            }

            if ($params[$field] === (string)$value) {
                unset($params[$field]);
            }
        }

        foreach ((array)$this->getConfig('queryStringWhitelist') as $field) {
            $value = $this->request->getQuery($field);
            if ($value !== null && !isset($params[$field])) {
                $params[$field] = $value;
            }
        }

        return $params;
    }
}
