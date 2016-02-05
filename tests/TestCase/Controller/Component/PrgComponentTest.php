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
    /**
     * Pre-test case setup
     */
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

    /**
     * Test the initialize method of the component with a get request
     *
     * @return void
     */
    public function testInitializeGet()
    {
        $expected = ['foo' => 'bar'];
        $this->Controller->request->query = $expected;

        $this->Prg->startup();
        $this->assertEquals($expected, $this->Controller->request->data);
    }

    /**
     * Data provider for testing the Component initialize
     *
     * The url must be set to what the Router will return
     *
     * @return void
     */
    public function providerRequest()
    {
        return [
            [
                ['controller' => 'posts', 'action' => 'index', 'pass' => ['pass']],
                ['foo' => 'bar'],
                '/index/pass',
                'http://localhost/index/pass?foo=bar'
            ],
            [
                ['controller' => 'Examples', 'action' => 'index'],
                ['foo' => 'bar'],
                '/examples',
                'http://localhost/examples?foo=bar'
            ],
            [
                ['controller' => 'UserAnswers', 'action' => 'index', 'type' => 'open'],
                ['question' => '', 'category' => 7, 'outcome' => ''],
                '/user-answers',
                'http://localhost/user-answers?question=&category=7&outcome='
            ],
        ];
    }

    /**
     * Test the initialize method of the Component with a post method
     *
     * @dataProvider providerRequest
     *
     * @param  array $requestParams Array of request params
     * @param  array $requestData   Array of search data
     * @param  string $url Request url
     * @param  string $expected     The expected url
     *
     * @return void
     */
    public function testInitializePost($requestParams, $requestData, $url, $expected)
    {
        $request = new Request([
            'url' => $url,
            'base' => '/',
            'params' => $requestParams,
            'environment' => ['REQUEST_METHOD' => 'POST']
        ]);
        $request->data = $requestData;

        $response = $this->getMock('Cake\Network\Response', ['stop']);

        $controller = new Controller($request, $response);
        $prg = new PrgComponent($controller->components());

        $response = $prg->startup();
        $this->assertEquals($expected, $response->header()['Location']);

        $prg->config('actions', false);
        $response = $prg->startup();
        $this->assertEquals(null, $response);

        $prg->config('actions', 'does-not-exist', false);
        $response = $prg->startup();
        $this->assertEquals(null, $response);

        $prg->config('actions', 'index', false);
        $response = $prg->startup();
        $this->assertEquals($expected, $response->header()['Location']);

        $prg->config('actions', ['index', 'does-not-exist'], false);
        $response = $prg->startup();
        $this->assertEquals($expected, $response->header()['Location']);

        $prg->config('actions', ['does-not-exist'], false);
        $response = $prg->startup();
        $this->assertEquals(null, $response);
    }
}
