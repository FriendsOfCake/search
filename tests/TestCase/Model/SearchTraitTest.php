<?php

namespace Search\Test\TestCase\Model;

use Cake\TestSuite\TestCase;
use Muffin\Webservice\AbstractDriver;
use Muffin\Webservice\Connection;
use Muffin\Webservice\Webservice\Webservice;
use Search\Manager;
use Search\Test\TestApp\Model\Endpoint\ArticlesEndpoint;

class SearchTraitTest extends TestCase
{
    /**
     * @var \Search\Test\TestApp\Model\Endpoint\ArticlesEndpoint
     */
    protected $Articles;

    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $webserviceMock = $this->getMockBuilder(Webservice::class)
            ->getMock();

        $driverMock = $this->getMockBuilder(AbstractDriver::class)
            ->getMockForAbstractClass();

        $connectionMock = $this->getMockBuilder(Connection::class)
            ->setConstructorArgs([
                [
                    'name' => 'test',
                    'driver' => $driverMock,
                ],
            ])
            ->setMethods(['webservice'])
            ->getMock();
        $connectionMock
            ->method('webservice')
            ->willReturn($webserviceMock);

        $this->Articles = new ArticlesEndpoint([
            'alias' => 'Articles',
            'connection' => $connectionMock,
        ]);
    }

    /**
     * Test the custom "search" finder
     *
     * @return void
     */
    public function testFinder()
    {
        $queryString = [
            'foo' => 'a',
            'public' => 'false',
        ];
        $this->assertFalse($this->Articles->isSearch());

        $query = $this->Articles->find('search', ['search' => $queryString]);
        $this->assertSame([
            'Articles.foo' => 'a',
            'Articles.public' => false,
        ], $query->where());
        $this->assertTrue($this->Articles->isSearch());
    }

    /**
     * testFindSearchException
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Custom finder "search" expects search arguments to be nested under key "search" in find() options.
     * @return void
     */
    public function testFindSearchException()
    {
        $this->Articles->find('search');
    }

    /**
     * testSearchManager
     *
     * @return void
     */
    public function testSearchManager()
    {
        $manager = $this->Articles->searchManager();
        $this->assertInstanceOf(Manager::class, $manager);
    }
}
