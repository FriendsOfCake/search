<?php
/**
 * Search Behavior
 *
 * @author   cake17
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://blog.cake-websites.com
 */
namespace Search\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;

class SearchBehavior extends Behavior
{

    /**
     * $_defaultConfig For the Behavior.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'filterCallback' => 'searchConfiguration',
        'filterCollection' => 'default',
        'implementedFinders' => [
            'search' => 'findSearch'
        ]
    ];

    /**
     * Callback fired from the controller.
     *
     * @param Query $query Query.
     * @param array $options The GET arguments.
     * @return \Cake\ORM\Query The Query object used in pagination.
     */
    public function findSearch(Query $query, array $options)
    {
        if (!isset($options['filterCollection'])) {
            $options['filterCollection'] = 'searchConfiguration';
        }
        foreach ($this->_table->{$this->config('filterCallback')}()->getFilters($this->config('filterCollection')) as $config) {
            $config->args($options);
            $config->query($query);
            $config->process();
        }

        return $query;
    }
}
