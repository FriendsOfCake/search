<?php

namespace Search\Model\Behavior;

use Cake\ORM\Behavior;
use Search\Model\SearchTrait;

class SearchBehavior extends Behavior
{
    use SearchTrait;

    /**
     * Default config for the behavior.
     *
     * You can overwrite default empty values using emptyValues key
     * when initializing the behavior
     *
     * @var array
     *
     */
    protected $_defaultConfig = [
        'implementedFinders' => [
            'search' => 'findSearch'
        ],
        'implementedMethods' => [
            'searchManager' => 'searchManager',
            'isSearch' => 'isSearch'
        ],
        'emptyValues' => ['', false, null]
    ];

    /**
     * Overwrite emptyValues config value
     *
     * @param array $config Config
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        if (isset($config['emptyValues'])) {
            $this->setConfig('emptyValues', $config['emptyValues'], false);
        }
    }

    /**
     * Returns the repository on which the filters should be applied.
     *
     * @return \Cake\ORM\Table
     */
    protected function _repository()
    {
        return $this->_table;
    }

    /**
     * Return the empty values.
     *
     * @return array
     */
    protected function _emptyValues()
    {
        return (array)$this->getConfig('emptyValues');
    }
}
