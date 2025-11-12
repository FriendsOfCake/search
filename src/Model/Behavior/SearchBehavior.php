<?php
declare(strict_types=1);

namespace Search\Model\Behavior;

use Cake\Core\Configure;
use Cake\ORM\Behavior;
use Cake\ORM\Table;
use Search\Model\Filter\FilterCollectionInterface;
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
     * - `emptyValues`: Values that should be considered empty and filtered out
     *   from search parameters
     * - `extraParams`: Additional parameters that should be preserved even if
     *   no filter is defined for them.
     * - `collectionClass`: Custom filter collection class to use for search filters
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'implementedFinders' => [
            'search' => 'findSearch',
        ],
        'implementedMethods' => [
            'searchManager' => 'searchManager',
            'isSearch' => 'isSearch',
            'searchParams' => 'searchParams',
        ],
        'emptyValues' => ['', false, null],
        'extraParams' => [],
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

        $defaultCollectionClass = sprintf(
            '%s\Model\Filter\%sCollection',
            Configure::read('App.namespace'),
            $this->table()->getAlias(),
        );
        if (class_exists($defaultCollectionClass)) {
            assert(is_subclass_of($defaultCollectionClass, FilterCollectionInterface::class));
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

    /**
     * Return the extra params.
     *
     * @return array
     */
    protected function _extraParams(): array
    {
        return $this->getConfig('extraParams', []);
    }
}
