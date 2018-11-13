<?php
namespace Search\Test\View\Helper;

use Cake\Http\ServerRequest;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use Search\View\Helper\SearchHelper;

class SearchHelperTest extends TestCase
{
    /**
     * @var \Search\View\Helper\SearchHelper
     */
    protected $searchHelper;

    /**
     * @var \Cake\View\View
     */
    protected $view;

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Router::reload();

        $this->view = new View();
        $config = [];
        $this->searchHelper = new SearchHelper($this->view, $config);
    }

    /**
     * @return void
     */
    public function testIsSearch()
    {
        $result = $this->searchHelper->isSearch();
        $this->assertFalse($result);

        $this->view->set('_isSearch', true);

        $result = $this->searchHelper->isSearch();
        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testResetUrl()
    {
        $result = $this->searchHelper->resetUrl();
        $this->assertSame(['?' => []], $result);

        $request = new ServerRequest('/controller/action?limit=5&sort=x&direction=asc&foo=baz&bar=1');
        $this->view = new View($request);
        $this->searchHelper = new SearchHelper($this->view, []);

        $params = [
            'foo' => 'baz',
            'bar' => '1',
        ];
        $this->view->set('_searchParams', $params);

        $result = $this->searchHelper->resetUrl();
        $expected = [
            '?' => [
                'limit' => '5',
                'sort' => 'x',
                'direction' => 'asc',
            ],
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @return void
     */
    public function testResetUrlWithPaginator()
    {
        $request = new ServerRequest('/controller/action?page=2&limit=5&sort=x&direction=asc&foo=bar');
        $request = $request->withParam('paging', ['Something']);

        $this->view = new View($request);
        $this->searchHelper = new SearchHelper($this->view, []);

        $params = [
            'foo' => 'bar',
        ];
        $this->view->set('_searchParams', $params);

        $result = $this->searchHelper->resetUrl();
        $expected = [
            '?' => [
                'limit' => '5',
                'sort' => 'x',
                'direction' => 'asc',
            ],
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @return void
     */
    public function testResetUrlWithPaginatorAndAdditionalBlacklist()
    {
        $request = new ServerRequest('/controller/action?page=2&limit=5&sort=x&direction=asc&foo=bar');
        $request = $request->withParam('paging', ['Something']);

        $this->view = new View($request);
        $config = [
            'additionalBlacklist' => [
                'sort',
                'direction',
            ],
        ];
        $this->searchHelper = new SearchHelper($this->view, $config);

        $params = [
            'foo' => 'bar',
        ];
        $this->view->set('_searchParams', $params);

        $result = $this->searchHelper->resetUrl();
        $expected = [
            '?' => [
                'limit' => '5',
            ],
        ];
        $this->assertSame($expected, $result);
    }

    /**
     * @return void
     */
    public function _testResetLink()
    {
        $result = $this->searchHelper->resetLink('Reset search filter', ['class' => 'button']);
        dd($result);
    }
}
