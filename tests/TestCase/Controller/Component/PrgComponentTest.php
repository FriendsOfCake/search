<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Controller\Component;

use Cake\Controller\Controller;
use Cake\Http\ServerRequest;
use Cake\ORM\Table;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Search\Controller\Component\PrgComponent;

class PrgComponentTest extends TestCase
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
    public function setUp(): void
    {
        parent::setUp();

        // Router::$initialized = true;
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
        $request = $this->Controller->getRequest()
            ->withAttribute('params', [
                'controller' => 'Posts',
                'action' => 'index',
                'pass' => ['pass'],
            ])
            ->withRequestTarget('/Posts/index/pass')
            ->withData('foo', 'bar')
            ->withEnv('REQUEST_METHOD', 'POST');

        $this->Controller->setRequest($request);

        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));

        $this->Prg->setConfig('actions', false);
        $response = $this->Prg->startup();
        $this->assertNull($response);

        $this->Prg->setConfig('actions', 'does-not-exist', false);
        $response = $this->Prg->startup();
        $this->assertNull($response);

        $this->Prg->setConfig('actions', 'index', false);
        $this->Controller->setResponse($this->Controller->getResponse()->withHeader('Location', ''));
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));

        $this->Prg->setConfig('actions', ['index', 'does-not-exist'], false);
        $this->Controller->setResponse($this->Controller->getResponse()->withHeader('Location', ''));
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));

        $this->Prg->setConfig('actions', true);
        $request = $this->Controller->getRequest()
            ->withAttribute('params', [
                'controller' => 'UserAnswers',
                'action' => 'index',
                'type' => 'open',
                'pass' => ['open'],
            ])
            ->withRequestTarget('/users/my-predictions');
        $this->Controller->setRequest($request);

        $this->Controller->setResponse($this->Controller->getResponse()->withHeader('Location', ''));
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/users/my-predictions?foo=bar', $response->getHeaderLine('Location'));

        $this->Controller->setRequest($this->Controller->getRequest()->withData('foo', ''));
        $this->Controller->setResponse($this->Controller->getResponse()->withHeader('Location', ''));
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/users/my-predictions', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testInitializePostWithEmptyValues()
    {
        $request = $this->Controller->getRequest()
            ->withAttribute('params', [
                'controller' => 'Posts',
                'action' => 'index',
                'pass' => ['pass'],
            ])
            ->withRequestTarget('/Posts/index/pass')
            ->withData('foo', 'bar')
            ->withData('checkbox', '0')
            ->withEnv('REQUEST_METHOD', 'POST');

        $this->Controller->setRequest($request);

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
        $request = $this->Controller->getRequest()
            ->withQueryParams([
                'sort' => 'created',
                'direction' => 'desc',
                'page' => 9,
            ])
            ->withAttribute('params', [
                'controller' => 'Posts',
                'action' => 'index',
                'pass' => ['pass'],
            ])
            ->withRequestTarget('/Posts/index/pass')
            ->withData('foo', 'bar')
            ->withEnv('REQUEST_METHOD', 'POST');

        $this->Controller->setRequest($request);

        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar&sort=created&direction=desc', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testInitializePostWithQueryStringWhitelistEmpty()
    {
        $request = $this->Controller->getRequest()
            ->withQueryParams([
                'sort' => 'created',
                'direction' => 'desc',
                'page' => 9,
            ])
            ->withAttribute('params', [
                'controller' => 'Posts',
                'action' => 'index',
                'pass' => ['pass'],
            ])
            ->withRequestTarget('/Posts/index/pass')
            ->withData('foo', 'bar')
            ->withEnv('REQUEST_METHOD', 'POST');

        $this->Controller->setRequest($request);

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
        $request = $this->Controller->getRequest()
            ->withAttribute('params', [
                'controller' => 'Posts',
                'action' => 'index',
                'pass' => ['pass'],
            ])
            ->withRequestTarget('/Posts/index/pass')
            ->withData('foo', 'bar')
            ->withEnv('REQUEST_METHOD', 'POST');

        $this->Controller->setRequest($request);

        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testIsSearchFalse()
    {
        $this->Controller->setRequest(
            $this->Controller->getRequest()->withAttribute('params', [
                'controller' => 'Articles',
                'action' => 'index',
            ])
        );
        $this->Controller->modelClass = 'Articles';
        $this->Controller->loadModel('Articles');
        $this->Controller->Articles->addBehavior('Search.Search');
        $this->Controller->Articles->find('search', ['search' => []]);

        $this->Prg->beforeRender();

        $viewVars = $this->Controller->viewBuilder()->getVars();
        $this->assertFalse($viewVars['_isSearch']);
    }

    /**
     * @return void
     */
    public function testIsSearchTrue()
    {
        $this->Controller->setRequest(
            $this->Controller->getRequest()->withAttribute('params', [
                'controller' => 'Articles',
                'action' => 'index',
            ])
        );
        $this->Controller->modelClass = 'SomePlugin.Articles';
        $this->Controller->Articles = $this->getMockBuilder(Table::class)->setMethods(['isSearch'])->getMock();
        $this->Controller->Articles->addBehavior('Search.Search');
        $this->Controller->Articles->expects($this->once())->method('isSearch')->willReturn(true);

        $this->Prg->beforeRender();

        $viewVars = $this->Controller->viewBuilder()->getVars();
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
