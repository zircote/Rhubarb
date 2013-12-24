<?php
namespace Rhubarb\Broker;

/**
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
 * @category    Tests
 * @subcategory AsyncResult
 */
use AMQP\Message;
use Rhubarb\Connector\PhpAmqp;
use Rhubarb\PhpAmqpTestCase;
use Rhubarb\Rhubarb;

/**
 * @package     Rhubarb
 * @category    Tests
 * @subcategory Rhubarb
 * @group PhpAmqp
 */
class PhpAmqpTest extends PhpAmqpTestCase
{

    /**
     * @var PhpAmqp
     */
    protected $fixture;

    public function getMockFixture()
    {
        $this->fixture = new PhpAmqp($this->rhubarb);
    }

    public function setUp()
    {
        $this->rhubarb = $this->getRhubarbMock($this->getBrokerMock(array(), array()));
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->rhubarb = null;
        $this->fixture = null;
    }

    public function testConstructor()
    {
        $this->getMockFixture();
        $this->assertInstanceOf('\Rhubarb\Rhubarb', $this->fixture->getRhubarb());
        $this->assertInstanceOf('\AMQPConnection', $this->fixture->getConnection());
    }

    /**
     *
     * 
     */
    public function testPublishTask()
    {
        /* @var \PHPUnit_Framework_MockObject_MockObject|PhpAmqp $mock */
        $mock = $this->getMock(
            '\Rhubarb\Broker\PhpAmqp',
            array('declareQueue', 'getExchange', 'getChannel', 'getConnection'),
            array($this->rhubarb, array())
        );
        $amqpConnection = $this->getAMQPConnectionMock(false);
        $amqpConnection->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue(false));
        $mock->expects($this->once())
            ->method('declareQueue')
            ->will($this->returnValue($this->getAMQPQueueMock()));
        $mock->expects($this->once())
            ->method('getExchange')
            ->will($this->returnValue($this->getAMQPExchangeMock(false, array('publish'), array())));
        $mock->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($this->getAMQPChannelMock()));
        $mock->expects($this->exactly(3))
            ->method('getConnection')
            ->will($this->returnValue($amqpConnection));
        $this->fixture = $mock;
        /* @var $amqpConnection \PHPUnit_Framework_MockObject_MockObject|\AMQPConnection */
        $this->fixture->setConnection($amqpConnection);
        $signature = $this->getSignatureMock(
            $this->rhubarb,
            array('getProperties', 'getHeaders'),
            array(),
            $this->getBodyMock(array(2, 1))
        );

        /* @var $message \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Message\Message */
        $message = $this->getMock(
            '\Rhubarb\Message\Message',
            array('getProperties', 'getHeaders'),
            array($this->rhubarb, $signature)
        );
        $message->expects($this->exactly(2))
            ->method('getProperties')
            ->will($this->returnValue(array()));
        $message->expects($this->exactly(2))
            ->method('getHeaders')
            ->will($this->returnValue(array()));

        /* So many mocks, I dont like it... */
        $this->fixture->publishTask($message);
    }
/**
    public function testGetHeaders()
    {
        $expected = array();
        $this->getMockFixture();
        $this->assertEquals($expected, $this->fixture->getHeaders());
    }

    /**
     * 
     */
    public function testGetProperties()
    {
        $expected = array(
            'content_type' => Rhubarb::CONTENT_TYPE_JSON,
            'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
            'delivery_mode' => PhpAmqp::AMQP_PERSISTENT,
            'priority' => 0
        );
        $this->getMockFixture();
        $this->assertEquals($expected, $this->fixture->getProperties());
    }
}
