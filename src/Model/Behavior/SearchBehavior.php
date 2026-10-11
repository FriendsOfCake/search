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
     * You can overwrite default empty values using emptyValues key
     * when initializing the behavior
     *
     * @var array<string, mixed>
     */
    protected array $defaultConfig = [
        'implementedFinders' => [
            'search' => 'findSearch',
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
            $this->collectionClass = $collectionClass;

            return;
        }

        $defaultCollectionClass = sprintf(
            '%s\Model\Filter\%sCollection',
            Configure::read('App.namespace'),
            $this->table->getAlias(),
        );
        if (class_exists($defaultCollectionClass)) {
            assert(is_subclass_of($defaultCollectionClass, FilterCollectionInterface::class));
            $this->collectionClass = $defaultCollectionClass;
        }
    }

    /**
     * Returns the repository on which the filters should be applied.
     *
     * @return \Cake\ORM\Table
     */
    protected function repository(): Table
    {
        return $this->table;
    }

    /**
     * Return the empty values.
     *
     * @return array
     */
    protected function emptyValues(): array
    {
        return $this->getConfig('emptyValues', []);
    }
}
