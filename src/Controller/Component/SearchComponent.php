<?php
declare(strict_types=1);

namespace Search\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;
use Closure;
use UnexpectedValueException;

/**
 * SearchComponent Component.
 *
 * Handles the search from submission and redirects to URL containing search params as query string.
 *
 * @see https://en.wikipedia.org/wiki/Post/Redirect/Get
 */
class SearchComponent extends Component
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
     *   (will not be passed along in the URL). Use closure for more control (return true for "empty").
     * - `modelClass` : Configure the controller's modelClass to be used for the query, used to
     *   populate the _isSearch view variable to allow for a reset button, for example.
     *   Set to false to disable the auto-setting of the view variable.
     * - `events`: List of events this component listens to. You can disable an
     *   event by setting it to false.
     *   E.g. `'events' => ['Controller.beforeRender' => false]`
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
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
     * @return array<string, mixed>
     */
    public function implementedEvents(): array
    {
        return Hash::filter($this->getConfig('events'));
    }

    /**
     * Checks if the current request has posted data and redirects the users
     * to the same action after converting the post data into GET params
     *
     * @param \Cake\Event\EventInterface $event Event instance
     * @return void
     */
    public function startup(EventInterface $event): void
    {
        if (!$this->getController()->getRequest()->is('post') || !$this->_isSearchAction()) {
            return;
        }

        $url = $this->getController()->getRequest()->getPath();

        $params = $this->_filterParams();
        if ($params) {
            $params = Hash::expand($params);
            $url .= '?' . http_build_query($params);
        }

        $event->setResult($this->_registry->getController()->redirect($url));
    }

    /**
     * Populates the $_isSearch view variable based on the current request.
     *
     * You need to configure the modelClass config if you are not using the controller's
     * default modelClass property.
     *
     * @return void
     */
    public function beforeRender(): void
    {
        if (!$this->_isSearchAction()) {
            return;
        }

        $controller = $this->getController();
        try {
            $model = $controller->fetchTable($this->getConfig('modelClass'));
        } catch (UnexpectedValueException $e) {
            return;
        }

        if (!$model->behaviors()->has('Search')) {
            return;
        }

        /** @phpstan-ignore method.notFound */
        $controller->set('_isSearch', $model->isSearch());
        /** @phpstan-ignore method.notFound */
        $controller->set('_searchParams', $model->searchParams());
    }

    /**
     * Checks if the action should be processed by the component.
     *
     * @return bool
     */
    protected function _isSearchAction(): bool
    {
        $actions = $this->getConfig('actions');
        if (is_bool($actions)) {
            return $actions;
        }

        return in_array($this->getController()->getRequest()->getParam('action'), (array)$actions, true);
    }

    /**
     * Filters the params from POST data and merges in the whitelisted query string ones.
     *
     * @return array
     */
    protected function _filterParams(): array
    {
        $params = Hash::filter((array)$this->getController()->getRequest()->getData());

        foreach ((array)$this->getConfig('queryStringBlacklist') as $field) {
            unset($params[$field]);
        }

        foreach ((array)$this->getConfig('emptyValues') as $field => $value) {
            if (!isset($params[$field])) {
                continue;
            }

            if ($value instanceof Closure) {
                $isEmpty = $value($params[$field], $params);
                if ($isEmpty) {
                    unset($params[$field]);
                }

                continue;
            }

            if ($params[$field] === (string)$value) {
                unset($params[$field]);
            }
        }

        foreach ((array)$this->getConfig('queryStringWhitelist') as $field) {
            $value = $this->getController()->getRequest()->getQuery($field);
            if ($value !== null && !isset($params[$field])) {
                $params[$field] = $value;
            }
        }

        return $params;
    }
}
