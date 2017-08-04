<?php
namespace Search\Test\TestCase\Controller\Component;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Search\Controller\Component\PrgComponent;

class SearchComponentTest extends TestCase
{
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
        $request = new Request();
        $response = $this
            ->getMockBuilder('Cake\Network\Response')
            ->setMethods(['stop'])
            ->getMock();

        $this->Controller = new Controller($request, $response);
        $this->Prg = new PrgComponent($this->Controller->components());
    }

    /**
     * @return void
     */
    public function testBeforeRenderGet()
    {
        $expected = ['foo' => 'bar'];
        $this->Controller->request->query = $expected;
        $this->Controller->request->action = 'index';

        $this->Prg->beforeRender(new Event('Controller.initialize'));
        $this->assertEquals($expected, $this->Controller->request->data);

        $this->Controller->request->data = [];
        $this->Prg->config('queryStringToData', false);

        $this->Prg->beforeRender(new Event('Controller.initialize'));
        $this->assertEquals([], $this->Controller->request->data);
    }

    /**
     * @return void
     */
    public function testInitializePost()
    {
        $this->Controller->request->params = [
            'controller' => 'Posts',
            'action' => 'index',
            'pass' => ['pass']
        ];
        $this->Controller->request->here = '/Posts/index/pass';
        $this->Controller->request->data = ['foo' => 'bar'];
        $this->Controller->request->env('REQUEST_METHOD', 'POST');

        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->header()['Location']);

        $this->Prg->config('actions', false);
        $response = $this->Prg->startup();
        $this->assertEquals(null, $response);

        $this->Prg->config('actions', 'does-not-exist', false);
        $response = $this->Prg->startup();
        $this->assertEquals(null, $response);

        $this->Prg->config('actions', 'index', false);
        $this->Controller->response->header('Location', '');
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->header()['Location']);

        $this->Prg->config('actions', ['index', 'does-not-exist'], false);
        $this->Controller->response->header('Location', '');
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->header()['Location']);

        $this->Prg->config('actions', true);
        $this->Controller->request->params = [
            'controller' => 'UserAnswers',
            'action' => 'index',
            'type' => 'open',
            'pass' => ['open']
        ];
        $this->Controller->request->here = '/users/my-predictions';
        $this->Controller->response->header('Location', '');
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/users/my-predictions?foo=bar', $response->header()['Location']);

        $this->Controller->request->data = ['foo' => ''];
        $this->Controller->response->header('Location', '');
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/users/my-predictions', $response->header()['Location']);
    }

    /**
     * @return void
     */
    public function testInitializePostWithEmptyValues()
    {
        $this->Controller->request->params = [
            'controller' => 'Posts',
            'action' => 'index',
            'pass' => ['pass']
        ];
        $this->Controller->request->here = '/Posts/index/pass';
        $this->Controller->request->data = ['foo' => 'bar', 'checkbox' => '0'];
        $this->Controller->request->env('REQUEST_METHOD', 'POST');

        $this->Prg->configShallow('emptyValues', [
            'checkbox' => '0',
        ]);
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->header()['Location']);
    }

    /**
     * @return void
     */
    public function testInitializePostWithQueryStringWhitelist()
    {
        $this->Controller->request->query = [
            'sort' => 'created',
            'direction' => 'desc',
            'page' => 9,
        ];
        $this->Controller->request->params = [
            'controller' => 'Posts',
            'action' => 'index',
            'pass' => ['pass']
        ];
        $this->Controller->request->here = '/Posts/index/pass';
        $this->Controller->request->data = ['foo' => 'bar'];
        $this->Controller->request->env('REQUEST_METHOD', 'POST');

        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar&sort=created&direction=desc', $response->header()['Location']);
    }

    /**
     * @return void
     */
    public function testInitializePostWithQueryStringWhitelistEmpty()
    {
        $this->Controller->request->query = [
            'sort' => 'created',
            'direction' => 'desc',
            'page' => 9,
        ];
        $this->Controller->request->params = [
            'controller' => 'Posts',
            'action' => 'index',
            'pass' => ['pass']
        ];
        $this->Controller->request->here = '/Posts/index/pass';
        $this->Controller->request->data = ['foo' => 'bar'];
        $this->Controller->request->env('REQUEST_METHOD', 'POST');

        // Needed as config() would not do anything here due to internal default behavior of merging here
        $this->Prg->configShallow('queryStringWhitelist', []);
        $response = $this->Prg->startup();
        $this->assertEquals('http://localhost/Posts/index/pass?foo=bar', $response->header()['Location']);
    }

    /**
     * @return void
     */
    public function testConversionWithoutRedirect()
    {
        $this->Controller->request->env('REQUEST_METHOD', 'POST');
        $this->assertNull($this->Prg->conversion(false));
    }
}
