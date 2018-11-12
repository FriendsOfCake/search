<?php
namespace Search\Test\TestCase\Controller\Component;

use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\ORM\Table;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Search\Controller\Component\PrgComponent;

class SearchComponentTest extends TestCase
{
    /**
     * @var array
     */
    public $fixtures = [
        'plugin.Search.Articles',
    ];

    /**
     * @var \Cake\Controller\Controller
     */
    public $Controller;

    /**
     * @var \Search\Controller\Component\PrgComponent
     */
    public $Prg;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        Router::$initialized = true;
        Router::scope('/', function (RouteBuilder $routes) {
            $routes->connect(
                '/users/my-predictions',
                ['controller' => 'UserAnswers', 'action' => 'index', 'type' => 'open'],
                ['pass' => ['type'], '_name' => 'userOpenPredictions']
            );
            $routes->fallbacks();
        });
        $request = new ServerRequest();
        $response = $this
            ->getMockBuilder('Cake\Http\Response')
            ->setMethods(['stop'])
            ->getMock();

        $this->Controller = new Controller($request, $response);
        $this->Prg = new PrgComponent($this->Controller->components());
    }

    /**
     * @return void
     */
    public function testInitializePost()
    {
        $this->Controller->request = $this->Controller->request->withAttribute('params', [
            'controller' => 'Posts',
            'action' => 'index',
            'pass' => ['pass'],
        ]);
        $this->Controller->request = $this->Controller->request->withRequestTarget('/Posts/index/pass');
        $this->Controller->request = $this->Controller->request->withData('foo', 'bar');
        $this->Controller->request = $this->Controller->request->withEnv('REQUEST_METHOD', 'POST');

        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));

        $this->Prg->setConfig('actions', false);
        $response = $this->Prg->startup();
        $this->assertNull($response);

        $this->Prg->setConfig('actions', 'does-not-exist', false);
        $response = $this->Prg->startup();
        $this->assertNull($response);

        $this->Prg->setConfig('actions', 'index', false);
        $this->Controller->response = $this->Controller->response->withHeader('Location', '');
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));

        $this->Prg->setConfig('actions', ['index', 'does-not-exist'], false);
        $this->Controller->response = $this->Controller->response->withHeader('Location', '');
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));

        $this->Prg->setConfig('actions', true);
        $this->Controller->request = $this->Controller->request->withAttribute('params', [
            'controller' => 'UserAnswers',
            'action' => 'index',
            'type' => 'open',
            'pass' => ['open'],
        ]);
        $this->Controller->request = $this->Controller->request->withRequestTarget('/users/my-predictions');
        $this->Controller->response = $this->Controller->response->withHeader('Location', '');
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/users/my-predictions?foo=bar', $response->getHeaderLine('Location'));

        $this->Controller->request = $this->Controller->request->withData('foo', '');
        $this->Controller->response = $this->Controller->response->withHeader('Location', '');
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/users/my-predictions', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testInitializePostWithEmptyValues()
    {
        $this->Controller->request = $this->Controller->request->withAttribute('params', [
            'controller' => 'Posts',
            'action' => 'index',
            'pass' => ['pass'],
        ]);
        $this->Controller->request = $this->Controller->request->withRequestTarget('/Posts/index/pass');
        $this->Controller->request = $this->Controller->request = $this->Controller->request = $this->Controller->request->withData('foo', 'bar');
        $this->Controller->request = $this->Controller->request = $this->Controller->request = $this->Controller->request->withData('checkbox', '0');
        $this->Controller->request = $this->Controller->request->withEnv('REQUEST_METHOD', 'POST');

        $this->Prg->configShallow('emptyValues', [
            'checkbox' => '0',
        ]);
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testInitializePostWithQueryStringWhitelist()
    {
        $this->Controller->request = $this->Controller->request->withQueryParams([
            'sort' => 'created',
            'direction' => 'desc',
            'page' => 9,
        ]);
        $this->Controller->request = $this->Controller->request->withAttribute('params', [
            'controller' => 'Posts',
            'action' => 'index',
            'pass' => ['pass'],
        ]);
        $this->Controller->request = $this->Controller->request->withRequestTarget('/Posts/index/pass');
        $this->Controller->request = $this->Controller->request->withData('foo', 'bar');
        $this->Controller->request = $this->Controller->request->withEnv('REQUEST_METHOD', 'POST');

        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar&sort=created&direction=desc', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testInitializePostWithQueryStringWhitelistEmpty()
    {
        $this->Controller->request = $this->Controller->request->withQueryParams([
            'sort' => 'created',
            'direction' => 'desc',
            'page' => 9,
        ]);
        $this->Controller->request = $this->Controller->request->withAttribute('params', [
            'controller' => 'Posts',
            'action' => 'index',
            'pass' => ['pass'],
        ]);
        $this->Controller->request = $this->Controller->request->withRequestTarget('/Posts/index/pass');
        $this->Controller->request = $this->Controller->request->withData('foo', 'bar');
        $this->Controller->request = $this->Controller->request->withEnv('REQUEST_METHOD', 'POST');

        // Needed as config() would not do anything here due to internal default behavior of merging here
        $this->Prg->configShallow('queryStringWhitelist', []);
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testConversionWithRedirect()
    {
        $this->Controller->request = $this->Controller->request->withAttribute('params', [
            'controller' => 'Posts',
            'action' => 'index',
            'pass' => ['pass'],
        ]);
        $this->Controller->request = $this->Controller->request->withRequestTarget('/Posts/index/pass');
        $this->Controller->request = $this->Controller->request->withData('foo', 'bar');
        $this->Controller->request = $this->Controller->request->withEnv('REQUEST_METHOD', 'POST');

        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testIsSearchFalse()
    {
        $this->Controller->request = $this->Controller->request->withAttribute('params', [
            'controller' => 'Articles',
            'action' => 'index',
        ]);
        $this->Controller->modelClass = 'Articles';
        $this->Controller->loadModel('Articles');
        $this->Controller->Articles->addBehavior('Search.Search');
        $this->Controller->Articles->find('search', ['search' => []]);

        $this->Prg->beforeRender();

        $viewVars = $this->Controller->viewVars;
        $this->assertFalse($viewVars['_isSearch']);
    }

    /**
     * @return void
     */
    public function testIsSearchTrue()
    {
        $this->Controller->request = $this->Controller->request->withAttribute('params', [
            'controller' => 'Articles',
            'action' => 'index',
        ]);
        $this->Controller->modelClass = 'SomePlugin.Articles';
        $this->Controller->Articles = $this->getMockBuilder(Table::class)->setMethods(['isSearch'])->getMock();
        $this->Controller->Articles->addBehavior('Search.Search');
        $this->Controller->Articles->expects($this->once())->method('isSearch')->willReturn(true);

        $this->Prg->beforeRender();

        $viewVars = $this->Controller->viewVars;
        $this->assertTrue($viewVars['_isSearch']);
    }

    /**
     * @return void
     */
    public function testEventsConfig()
    {
        $expected = [
            'Controller.startup' => 'startup',
            'Controller.beforeRender' => 'beforeRender',
        ];

        $result = $this->Prg->implementedEvents();
        $this->assertSame($expected, $result);

        $events = [
            'Controller.startup' => 'startup',
            'Controller.beforeRender' => false,
        ];
        $this->Prg->setConfig(['events' => $events]);

        $result = $this->Prg->implementedEvents();
        $this->assertSame(['Controller.startup' => 'startup'], $result);
    }
}
