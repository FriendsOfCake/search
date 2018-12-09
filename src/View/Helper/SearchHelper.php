<?php
namespace Search\View\Helper;

use Cake\View\Helper;
use Cake\View\View;

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
     * Request instance.
     *
     * @var \Cake\Http\ServerRequest;
     */
    public $request;

    /**
     * Default config for this class
     *
     * - 'additionalBlacklist': Additional params that also should be filtered out.
     *   For pagination views that usually is also limit and page, as the offset would be wrong.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'additionalBlacklist' => [],
    ];

    /**
     * Checks for pagination and if so, blacklist limit and page params.
     *
     * @param \Cake\View\View $View View
     * @param array $config Config
     */
    public function __construct(View $View, array $config)
    {
        if (method_exists($View, 'getRequest')) {
            $this->request = $View->getRequest();
        } else {
            $this->request = $View->request;
        }
        if ($this->request->getParam('paging')) {
            $this->_defaultConfig['additionalBlacklist'][] = 'page';
        }

        parent::__construct($View, $config);
    }

    /**
     * Returns true if the current request has at least one search filter applied.
     *
     * @return bool
     */
    public function isSearch()
    {
        return (bool)$this->_View->get('_isSearch');
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
        return $this->Html->link($label, $this->resetUrl(), $options);
    }

    /**
     * Returns the cleaned URL.
     *
     * @return array URL with cleaned Query string.
     */
    public function resetUrl()
    {
        $query = $this->request->getQuery();

        $searchParams = (array)$this->_View->get('_searchParams');
        $query = array_diff_key($query, $searchParams);

        $additionalBlacklist = (array)$this->getConfig('additionalBlacklist');
        foreach ($additionalBlacklist as $param) {
            unset($query[$param]);
        }

        return [
            '?' => $query,
        ];
    }
}
