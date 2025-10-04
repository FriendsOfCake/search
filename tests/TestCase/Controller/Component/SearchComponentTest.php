<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Controller\Component;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\ORM\Table;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use ReflectionProperty;
use Search\Controller\Component\SearchComponent;
use Search\Test\TestApp\Form\SearchForm;

class SearchComponentTest extends TestCase
{
    protected array $fixtures = [
        'plugin.Search.Articles',
    ];

    protected Controller $Controller;

    protected SearchComponent $Search;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Router::createRouteBuilder('/')->scope('/', function (RouteBuilder $routes) {
            $routes->connect(
                '/users/my-predictions',
                ['controller' => 'UserAnswers', 'action' => 'index', 'type' => 'open'],
                ['pass' => ['type'], '_name' => 'userOpenPredictions'],
            );
            $routes->fallbacks();
        });
        $request = new ServerRequest();

        $this->Controller = new Controller($request);
        $reflection = new ReflectionProperty(Controller::class, 'defaultTable');
        $reflection->setValue($this->Controller, 'Articles');

        $this->Search = new SearchComponent($this->Controller->components());
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
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $response = $event->getResult();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));

        $this->Search->setConfig('actions', false);
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $this->assertNull($event->getResult());

        $this->Search->setConfig('actions', 'does-not-exist', false);
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $this->assertNull($event->getResult());

        $this->Search->setConfig('actions', 'index', false);
        $this->Controller->setResponse($this->Controller->getResponse()->withHeader('Location', ''));
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $response = $event->getResult();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));

        $this->Search->setConfig('actions', ['index', 'does-not-exist'], false);
        $this->Controller->setResponse($this->Controller->getResponse()->withHeader('Location', ''));
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $response = $event->getResult();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));

        $this->Search->setConfig('actions', true);
        $request = $this->Controller->getRequest()
            ->withAttribute('params', [
                'controller' => 'UserAnswers',
                'action' => 'index',
                'type' => 'open',
                'pass' => ['open'],
            ])
            ->withRequestTarget('/users/my-predictions')
            ->withData('foo', 'bar');
        $this->Controller->setRequest($request);

        $this->Controller->setResponse($this->Controller->getResponse()->withHeader('Location', ''));
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $response = $event->getResult();
        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('http://localhost/users/my-predictions?foo=bar', $response->getHeaderLine('Location'));

        $this->Controller->setRequest($this->Controller->getRequest()->withData('foo', ''));
        $this->Controller->setResponse($this->Controller->getResponse()->withHeader('Location', ''));
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $response = $event->getResult();
        $this->assertInstanceOf(Response::class, $response);
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
        $event = new Event('Controller.startup', $this->Controller);

        $this->Search->configShallow('emptyValues', [
            'checkbox' => '0',
        ]);
        $this->Search->startup($event);
        $response = $event->getResult();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testInitializePostWithEmptyValuesCallable()
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
        $event = new Event('Controller.startup', $this->Controller);

        $this->Search->configShallow('emptyValues', [
            'checkbox' => function ($value, array $params): bool {
                return $value === '0';
            },
        ]);
        $this->Search->startup($event);
        $response = $event->getResult();

        $this->assertInstanceOf(Response::class, $response);
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
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $response = $event->getResult();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar&sort=created&direction=desc', $response->getHeaderLine('Location'));
    }

    /**
     * @return void
     */
    public function testInitializePostWithNestedQueryString()
    {
        $request = $this->Controller->getRequest()
            ->withQueryParams([
                'scope' => [
                    'sort' => 'created',
                    'direction' => 'desc',
                    'page' => 9,
                ],
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
        $event = new Event('Controller.startup', $this->Controller);

        $this->Search->configShallow('queryStringWhitelist', ['scope.sort', 'scope.direction']);
        $this->Search->startup($event);
        $response = $event->getResult();

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar&scope%5Bsort%5D=created&scope%5Bdirection%5D=desc', $response->getHeaderLine('Location'));
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
        $this->Search->configShallow('queryStringWhitelist', []);
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $response = $event->getResult();
        $this->assertInstanceOf(Response::class, $response);
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
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $response = $event->getResult();

        $this->assertInstanceOf(Response::class, $response);
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
            ]),
        );

        if (method_exists($this->Controller, 'fetchTable')) {
            $this->Controller->fetchTable()->addBehavior('Search.Search');
            $this->Controller->fetchTable()->find('search', ['search' => []]);
        } else {
            $this->Controller->modelClass = 'Articles';
            $this->Controller->loadModel('Articles');
            $this->Controller->Articles->addBehavior('Search.Search');
            $this->Controller->Articles->find('search', ['search' => []]);
        }

        $this->Search->beforeRender();

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
            ]),
        );

        $articles = $this->getMockBuilder(Table::class)->addMethods(['isSearch'])->getMock();
        $articles->addBehavior('Search.Search');
        $articles->expects($this->once())->method('isSearch')->willReturn(true);

        if (method_exists($this->Controller, 'fetchTable')) {
            $this->Controller->getTableLocator()->set('Articles', $articles);
        } else {
            $this->Controller->modelClass = 'SomePlugin.Articles';
            $this->Controller->Articles = $articles;
        }

        $this->Search->beforeRender();

        $viewVars = $this->Controller->viewBuilder()->getVars();
        $this->assertTrue($viewVars['_isSearch']);
    }

    public function testGetWithForm(): void
    {
        $request = $this->Controller->getRequest()
            ->withAttribute('params', [
                'controller' => 'Posts',
                'action' => 'index',
            ])
            ->withRequestTarget('/Posts')
            ->withEnv('REQUEST_METHOD', 'GET');

        $this->Search->setConfig('formClass', SearchForm::class);

        $this->Controller->setRequest($request);
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $this->assertNull($event->getResult());

        $this->assertInstanceOf(SearchForm::class, $this->Controller->viewBuilder()->getVar('searchForm'));
    }

    public function testPostWithFormValidation(): void
    {
        $request = $this->Controller->getRequest()
            ->withAttribute('params', [
                'controller' => 'Posts',
                'action' => 'index',
            ])
            ->withRequestTarget('/Posts')
            ->withData('q', 'ab') // too short, validation should fail
            ->withEnv('REQUEST_METHOD', 'POST');

        $this->Search->setConfig('formClass', SearchForm::class);

        $this->Controller->setRequest($request);
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);
        $this->assertNull($event->getResult());

        /** @var \Cake\Form\Form $form */
        $form = $this->Controller->viewBuilder()->getVar('searchForm');
        $this->assertInstanceOf(SearchForm::class, $form);

        $errors = [
            'q' => [
                'minLength' => 'Search query must be at least 3 characters long',
            ],
        ];
        $this->assertEquals($errors, $form->getErrors());
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

        $result = $this->Search->implementedEvents();
        $this->assertSame($expected, $result);

        $events = [
            'Controller.startup' => 'startup',
            'Controller.beforeRender' => false,
        ];
        $this->Search->setConfig(['events' => $events]);

        $result = $this->Search->implementedEvents();
        $this->assertSame(['Controller.startup' => 'startup'], $result);
    }
}
