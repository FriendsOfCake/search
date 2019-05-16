<?php
namespace Search\Model\Behavior;

use Cake\Core\Configure;
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
            'search' => 'findSearch',
        ],
        'implementedMethods' => [
            'searchManager' => 'searchManager',
            'isSearch' => 'isSearch',
            'searchParams' => 'searchParams',
        ],
        'emptyValues' => ['', false, null],
        'collectionClass' => null,
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

        $collectionClass = $this->getConfig('collectionClass');
        if ($collectionClass) {
            $this->_collectionClass = $collectionClass;

            return;
        }

        $defaultCollectionClass = sprintf(
            '%s\Model\Filter\%sCollection',
            Configure::read('App.namespace'),
            $this->getTable()->getAlias()
        );
        if (class_exists($defaultCollectionClass)) {
            $this->_collectionClass = $defaultCollectionClass;
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
     * @return array|null
     */
    protected function _emptyValues()
    {
        return $this->getConfig('emptyValues');
    }
}
