<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Controller\Component;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use ReflectionProperty;
use RuntimeException;
use Search\Controller\Component\SearchComponent;
use Search\Test\TestApp\Form\SearchForm;
use Search\Test\TestApp\Model\Table\ArticlesTable;

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

        $this->Controller->fetchTable()->addBehavior('Search.Search');
        $this->Controller->fetchTable()->find('search', ['search' => []]);

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

        $articles = $this->getTableLocator()->get('Articles', [
            'className' => 'Search\Test\TestApp\Model\Table\ArticlesTable',
        ]);
        $articles->addBehavior('Search.Search');

        // Mock the isSearch method behavior
        $behavior = $articles->behaviors()->get('Search');
        $reflection = new ReflectionProperty($behavior, '_isSearch');
        $reflection->setValue($behavior, true);

        $this->Controller->getTableLocator()->set('Articles', $articles);

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

    public function testPostFormValidationFailure(): void
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
        $this->assertSame($errors, $form->getErrors());
    }

    public function testPostFormValidationSuccess(): void
    {
        $request = $this->Controller->getRequest()
            ->withAttribute('params', [
                'controller' => 'Posts',
                'action' => 'index',
            ])
            ->withRequestTarget('/Posts')
            ->withData('q', 'abcd')
            ->withEnv('REQUEST_METHOD', 'POST');

        $mock = new class extends SearchForm
        {
            public static $called = false;

            protected function _execute(array $data = []): bool
            {
                static::$called = true;

                return true;
            }
        };

        $this->Search->setConfig('formClass', $mock::class);

        $this->Controller->setRequest($request);
        $event = new Event('Controller.startup', $this->Controller);
        $this->Search->startup($event);

        $result = $event->getResult();
        $this->assertInstanceOf(Response::class, $result);

        $this->assertTrue($mock::$called, 'Form execute method was not called');

        // View var is not set as there will be a redirect
        $this->assertNull($this->Controller->viewBuilder()->getVar('searchForm'));
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

    /**
     * Test that autoloadHelper is true by default and loads the Search helper
     *
     * @return void
     */
    public function testAutoloadHelperDefault()
    {
        $this->Controller->setRequest(
            $this->Controller->getRequest()->withAttribute('params', [
                'controller' => 'Articles',
                'action' => 'index',
            ]),
        );

        $articles = $this->getTableLocator()->get('Articles', [
            'className' => ArticlesTable::class,
        ]);
        $articles->addBehavior('Search.Search');

        $this->Controller->getTableLocator()->set('Articles', $articles);

        $this->Search->beforeRender();

        $helpers = $this->Controller->viewBuilder()->getHelpers();
        $this->assertArrayHasKey('Search', $helpers);
    }

    /**
     * Test that autoloadHelper can be disabled with false
     *
     * @return void
     */
    public function testAutoloadHelperDisabled()
    {
        $this->Controller->setRequest(
            $this->Controller->getRequest()->withAttribute('params', [
                'controller' => 'Articles',
                'action' => 'index',
            ]),
        );

        $articles = $this->getTableLocator()->get('Articles', [
            'className' => ArticlesTable::class,
        ]);
        $articles->addBehavior('Search.Search');

        $this->Controller->getTableLocator()->set('Articles', $articles);

        $this->Search->setConfig('autoloadHelper', false);
        $this->Search->beforeRender();

        $helpers = $this->Controller->viewBuilder()->getHelpers();
        $this->assertArrayNotHasKey('Search', $helpers);
    }

    /**
     * Test that autoloadHelper accepts array configuration for the helper
     *
     * @return void
     */
    public function testAutoloadHelperWithConfig()
    {
        $this->Controller->setRequest(
            $this->Controller->getRequest()->withAttribute('params', [
                'controller' => 'Articles',
                'action' => 'index',
            ]),
        );

        $articles = $this->getTableLocator()->get('Articles', [
            'className' => ArticlesTable::class,
        ]);
        $articles->addBehavior('Search.Search');

        $this->Controller->getTableLocator()->set('Articles', $articles);

        $helperConfig = ['additionalBlacklist' => ['foo']];
        $this->Search->setConfig('autoloadHelper', $helperConfig);
        $this->Search->beforeRender();

        $helpers = $this->Controller->viewBuilder()->getHelpers();
        $this->assertArrayHasKey('Search', $helpers);
        $this->assertSame('Search.Search', $helpers['Search']['className']);
        $this->assertSame(['foo'], $helpers['Search']['additionalBlacklist']);
    }

    /**
     * Test that without strictMode, missing model silently skips.
     *
     * @return void
     */
    public function testBeforeRenderWithoutModelSilentlySkips(): void
    {
        $controller = new Controller(
            $this->Controller->getRequest()->withAttribute('params', [
                'controller' => 'NonExistent',
                'action' => 'index',
            ]),
        );
        $reflection = new ReflectionProperty(Controller::class, 'defaultTable');
        $reflection->setValue($controller, null);

        $search = new SearchComponent($controller->components());
        $search->beforeRender();

        $viewVars = $controller->viewBuilder()->getVars();
        $this->assertArrayNotHasKey('_isSearch', $viewVars);
    }

    /**
     * Test that without strictMode, missing Search behavior silently skips.
     *
     * @return void
     */
    public function testBeforeRenderWithoutBehaviorSilentlySkips(): void
    {
        $this->Controller->setRequest(
            $this->Controller->getRequest()->withAttribute('params', [
                'controller' => 'Articles',
                'action' => 'index',
            ]),
        );

        // Table exists but Search behavior is NOT loaded
        $articles = $this->getTableLocator()->get('Articles', [
            'className' => ArticlesTable::class,
        ]);
        $this->Controller->getTableLocator()->set('Articles', $articles);

        $this->Search->beforeRender();

        $viewVars = $this->Controller->viewBuilder()->getVars();
        $this->assertArrayNotHasKey('_isSearch', $viewVars);
    }

    /**
     * Test that strictMode throws exception when model cannot be fetched.
     *
     * @return void
     */
    public function testStrictModeThrowsOnMissingModel(): void
    {
        $controller = new Controller(
            $this->Controller->getRequest()->withAttribute('params', [
                'controller' => 'NonExistent',
                'action' => 'index',
            ]),
        );
        $reflection = new ReflectionProperty(Controller::class, 'defaultTable');
        $reflection->setValue($controller, null);

        $search = new SearchComponent($controller->components(), [
            'strictMode' => true,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('could not load table');
        $search->beforeRender();
    }

    /**
     * Test that strictMode throws exception when Search behavior is not loaded.
     *
     * @return void
     */
    public function testStrictModeThrowsOnMissingBehavior(): void
    {
        $this->Controller->setRequest(
            $this->Controller->getRequest()->withAttribute('params', [
                'controller' => 'Articles',
                'action' => 'index',
            ]),
        );

        // Table exists but Search behavior is NOT loaded
        $articles = $this->getTableLocator()->get('Articles', [
            'className' => ArticlesTable::class,
        ]);
        $this->Controller->getTableLocator()->set('Articles', $articles);

        $this->Search->setConfig('strictMode', true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not have the Search behavior loaded');
        $this->Search->beforeRender();
    }
}
