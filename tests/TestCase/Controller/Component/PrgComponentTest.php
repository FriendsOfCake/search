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
            ->setMethods(['is', 'method'])
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
     * testInitializeWithPost
     *
     * @return void
     */
    public function testInitializeWithPost()
    {
        $this->request->expects($this->at(0))
            ->method('is')
            ->with(['get'])
            ->will($this->returnValue(false));

        $this->request->expects($this->at(1))
            ->method('is')
            ->with(['post', 'put', 'delete'])
            ->will($this->returnValue(true));

        $this->controller->expects($this->at(0))
            ->method('redirect')
            ->with(['?' => ['title' => 'foobar']])
            ->will($this->returnValue(true));

        $this->request->data = ['title' => 'foobar'];
        $this->controller->request = $this->request;

        $PrgComponent = new PrgComponent($this->registry);
        $PrgComponent->initialize([]);
    }

     /**
      * testInitializeWithGet
      *
      * @return void
      */
     public function testInitializeWithGet()
     {
         $this->request->expects($this->at(0))
             ->method('is')
             ->with(['get'])
             ->will($this->returnValue(true));

         $this->request->query = ['?' => ['title' => 'foobar']];
         $this->controller->request = $this->request;
    
         $PrgComponent = new PrgComponent($this->registry);
         $PrgComponent->initialize([]);

         $this->assertEquals($this->request->data, ['?' => ['title' => 'foobar']]);
     }
}
