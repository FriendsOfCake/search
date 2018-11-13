<?php
namespace Search\View\Helper;

use Cake\View\Helper;

class SearchHelper extends Helper
{
    /**
     * @var array
     */
    public $helpers = [
        'Url',
        'Html',
    ];

    /**
     * @return bool
     */
    public function isSearch() {
        return $this->_View->get('_isSearch');
    }

    /**
     * @param string $label
     * @param array $attributes
     * @return string HTML
     */
    public function resetLink($label, array $attributes = [])
    {
        return $this->Html->link($label, $this->resetUrlArray(), $attributes);
    }

    /**
     * @return array
     */
    public function resetUrlArray()
    {
        $query = $this->request->getQuery();

        $searchParams = $this->_View->get('_searchParams');
        foreach ($searchParams as $searchParam => $value) {
            unset($query[$searchParam]);
        }

        return [
            '?' => $query,
        ];
    }
}
