<?php
namespace Rhubarb\Connector;

/**
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2013] [Robert Allen]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package     Rhubarb
 * @category    RhubarbTests\Connector
 */
use Rhubarb\PhpAmqpTestCase;

/**
 * @package     Rhubarb
 * @category    RhubarbTests\Connector
 */
class PhpAmqpTest extends PhpAmqpTestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PhpAmqp
     */
    protected $fixture;

    public function setUp()
    {
        $this->rhubarb = $this->getRhubarbMock($this->getBrokerMock(array(), array()));
        $options = array('connection' => $this->getAMQPConnectionMock());
        $this->fixture = new PhpAmqp($this->rhubarb, $options);
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->rhubarb = null;
        $this->fixture = null;
    }

    /**
     * @group AMQP
     *
     * @group ConnectorC
     */
    public function testConstructor()
    {
        $amqp = new PhpAmqp(
            $this->rhubarb,
            array('connection' =>
                'amqp://guest:guest@localhost:5672/celery?read_timeout=2.3&write_timeout=5')
        );
        $this->assertEquals('localhost', $amqp->getConnection()->getHost());
        $this->assertEquals('guest', $amqp->getConnection()->getLogin());
        $this->assertEquals('guest', $amqp->getConnection()->getPassword());
        $this->assertEquals('celery', $amqp->getConnection()->getVhost());
        $this->assertEquals(5672, $amqp->getConnection()->getPort());
        $this->assertEquals(2.3, $amqp->getConnection()->getReadTimeout());
        $this->assertEquals(5, $amqp->getConnection()->getWriteTimeout());
    }

    public function testConnectTrue()
    {
        $AMQPConnection = $this->getAMQPConnectionMock(false, array('connect'));
        $AMQPConnection->expects($this->once())
            ->method('connect')
            ->will($this->returnValue(true));

        $this->fixture->setConnection($AMQPConnection);

        $this->assertTrue($this->fixture->connect());
    }

    public function testConnectFalse()
    {
        $AMQPConnection = $this->getAMQPConnectionMock(false, array('connect'));
        $AMQPConnection->expects($this->once())
            ->method('connect')
            ->will($this->returnValue(false));

        $this->fixture->setConnection($AMQPConnection);

        $this->assertFalse($this->fixture->connect());
    }

    public function testDisconnect()
    {
        $AMQPConnection = $this->getAMQPConnectionMock(false, array('disconnect'));
        $AMQPConnection->expects($this->once())
            ->method('disconnect')
            ->will($this->returnValue(true));

        $this->fixture->setConnection($AMQPConnection);

        $this->assertTrue($this->fixture->disconnect());
    }

    public function testReconnect()
    {
        $AMQPConnection = $this->getAMQPConnectionMock(false, array('reconnect'));
        $AMQPConnection->expects($this->once())
            ->method('reconnect')
            ->will($this->returnValue(true));

        $this->fixture->setConnection($AMQPConnection);

        $this->assertTrue($this->fixture->reconnect());
    }

    public function testIsConnected()
    {
        $AMQPConnection = $this->getAMQPConnectionMock(false, array('isConnected'));
        $AMQPConnection->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(false));

        $this->fixture->setConnection($AMQPConnection);

        $this->assertFalse($this->fixture->isConnected());
    }
}
