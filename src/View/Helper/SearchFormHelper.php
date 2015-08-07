<?php
namespace Search\View\Helper;

use Cake\View\Helper;
use Cake\View\View;
use Cake\ORM\TableRegistry;


/**
 * SearchForm helper
 */
class SearchFormHelper extends Helper
{

    public $helpers = ['Form', 'Html'];

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Output a complete search form, based on the search configurations set up
     * in the specified table
     *
     * @param $model string The Table to load
     * @return null|string An formatted FORM with inputs.
     */
    public function form($model)
    {
        $table = TableRegistry::get($model);

        // make sure the table has the behavior and has implemented the searchConfiguration method
        if (
            ! $table->behaviors()->has('Search') ||
            ! method_exists($table, 'searchConfiguration')
        ) {
            return null;
        }

        $searchConfig = $table->searchConfiguration()->all();

        $output = $this->Html->tag('h3', __('Search'));
        $output .= $this->Form->create();

        foreach ($searchConfig as $object) {
            $config = $object->config();

            // you can add stuff like `'multiple' => true` or `'empty' => ' '`
            // to the individual searchConfiguration field calls to be passed to
            // the form input
            $output .= $this->Form->input($config['name'], $config);
        }

        $output .= $this->Form->submit();
        $output .= $this->Form->end();

        return $output;
    }
}
