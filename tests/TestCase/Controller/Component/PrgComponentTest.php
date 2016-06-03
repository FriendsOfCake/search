<?php
namespace Search\Test\TestCase\Controller\Component;

use Cake\Controller\Controller;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Search\Controller\Component\PrgComponent;

class SearchComponentTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        Router::$initialized = true;
        Router::scope('/', function ($routes) {
            $routes->connect(
                '/users/my-predictions',
                ['controller' => 'UserAnswers', 'action' => 'index', 'type' => 'open'],
                ['pass' => ['type'], '_name' => 'userOpenPredictions']
            );
            $routes->fallbacks();
        });
        $request = new Request();
        $response = $this->getMock('Cake\Network\Response', ['stop']);

        $this->Controller = new Controller($request, $response);
        $this->Prg = new PrgComponent($this->Controller->components());
    }

    public function testInitializeGet()
    {
        $expected = ['foo' => 'bar'];
        $this->Controller->request->query = $expected;
        $this->Controller->request->action = 'index';

        $this->Prg->startup();
        $this->assertEquals($expected, $this->Controller->request->data);
    }

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
}
