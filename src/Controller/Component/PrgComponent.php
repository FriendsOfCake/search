<?php
namespace FOC\Search\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\Event;

class PrgComponent extends Component
{

    /**
     * Preset Var.
     *
     * @var bool
     */
    public $presetVar = true;

    /**
     * Initialize properties.
     *
     * @param array $config The config data.
     * @return void
     */
    public function initialize(array $config)
    {
        $controller = $this->_registry->getController();
        $request = $controller->request;

        if (!$request->is('post')) {
            $request->data = $request->query;
            return;
        }

        $controller->redirect(['?' => $request->data]);
    }
}
