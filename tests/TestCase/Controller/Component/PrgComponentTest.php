<?php
namespace Burzum\Search\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Network\Http\Request;
use Burzum\Search\Controller\Component\PrgComponent;
use Cake\TestSuite\TestCase;

class PrgComponentTest extends TestCase {

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [];

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->request = $this->getMockBuilder('\Cake\Network\Http\Request')
            ->setMethods(['is'])
            ->getMock();
        $this->controller = $this->getMockBuilder('\Cake\Controller\Controller')
            ->setMethods(['redirect'])
            ->getMock();
        $this->registry = new ComponentRegistry($this->controller);
    }

    /**
     * tearDown
     *
     * @return void
     */
    public function tearDown()
    {
    }

    /**
     * 
     */
    public function testInitialize()
    {
        $this->request->expects($this->any())
            ->method('is')
            ->with('post')
            ->will($this->returnValue(true));

        $this->request->data = ['title' => 'foobar'];

        $this->controller->request = $this->request;

        $PrgComponent = new PrgComponent($this->registry);
        $PrgComponent->initialize([]);

        $this->controller->expects($this->any(1))
            ->method('redirect')
            ->with(['?' => ['title' => 'foobar']]);
    }
}
