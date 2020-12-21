<?php
declare(strict_types=1);

namespace Search\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Search\Model\SearchTrait;

/**
 * Search Behaviors.
 *
 * Allows configuring the search manager and provides the "search" finder.
 */
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
    public function initialize(array $config): void
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

        $tableMethod = method_exists($this, 'table') ? 'table' : 'getTable';

        /** @psalm-var class-string<\Search\Model\Filter\FilterCollectionInterface> */
        $defaultCollectionClass = sprintf(
            '%s\Model\Filter\%sCollection',
            Configure::read('App.namespace'),
            $this->{$tableMethod}()->getAlias()
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
    protected function _repository(): Table
    {
        return $this->_table;
    }

    /**
     * Return the empty values.
     *
     * @return array|null
     */
    protected function _emptyValues(): ?array
    {
        return $this->getConfig('emptyValues');
    }
}
