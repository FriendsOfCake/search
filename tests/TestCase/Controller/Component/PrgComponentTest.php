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

        $this->Prg->initialize([]);
        $this->assertEquals($expected, $this->Controller->request->data);
    }

    public function testInitializePost()
    {
        $this->Controller->request->params = [
            'controller' => 'posts',
            'action' => 'index',
            'pass' => ['pass']
        ];
        $this->Controller->request->data = ['foo' => 'bar'];
        $this->Controller->request->env('REQUEST_METHOD', 'POST');

        $response = $this->Prg->initialize([]);
        $this->assertEquals('http://localhost/index/pass?foo=bar', $response->header()['Location']);
    }
}
