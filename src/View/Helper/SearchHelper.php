<?php
namespace Search\View\Helper;

use Cake\View\Helper;

/**
 * @property \Cake\View\Helper\UrlHelper $Url
 * @property \Cake\View\Helper\HtmlHelper $Html
 */
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
     * Returns true if the current request has at least one search filter applied.
     *
     * @return bool
     */
    public function isSearch()
    {
        return $this->_View->get('_isSearch');
    }

    /**
     * Returns a reset link for the search form.
     *
     * @param string $label Label text.
     * @param array $options Array of options and HTML attributes.
     * @return string HTML.
     */
    public function resetLink($label, array $options = [])
    {
        return $this->Html->link($label, $this->resetUrlArray(), $options);
    }

    /**
     * Returns the cleaned URL.
     *
     * @return array URL with cleaned Query string.
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
