<?php
namespace Rhubarb\ResultStore;

/**
 *
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2012-2014], [Robert Allen]
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
 * @category    RhubarbTests\Result
 */
use Rhubarb\Rhubarb;
use Rhubarb\RhubarbTestCase;
use Rhubarb\Task\AsyncResult;

/**
 * @package     Rhubarb
 * @category    RhubarbTests\Result
 * @group Rhubarb
 * @group Rhubarb\ResultStore
 * @group Rhubarb\ResultStore\PhpAmqp
 */
class PhpAmqpTest extends RhubarbTestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject| PhpAmqp
     */
    protected $fixture;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject| \AMQPQueue
     */
    protected $queue;

    public function mockUpFixture()
    {
        $result = '{ "state": "SUCCESS", "traceback": null, "result": 4, "children": [] }';

        $this->rhubarb = $this->getRhubarbMock($this->getBrokerMock(array()));
        $AMQPEnvelope = $this->getMock('\AMQPEnvelope', array('getBody', 'getHeader'), array(), '', false);
        $AMQPEnvelope->expects($this->any())->method('getBody')->will($this->returnValue($result));
        $AMQPEnvelope->expects($this->any())->method('getHeader')->will($this->returnValue(Rhubarb::CONTENT_TYPE_JSON));


        $this->queue = $this->getMock('\AMQPQueue', array('get', 'ack', 'delete'), array(), '', false);
        $this->queue->expects($this->once())->method('ack');
        $this->queue->expects($this->once())->method('delete');
        $this->queue->expects($this->once())->method('get')->will($this->returnValue($AMQPEnvelope));

        $connection = $this->getMock('\AMQPConnection', array('connect'), array(), '', false);
        $connection->expects($this->once())->method('connect')->will($this->returnValue(true));

        $this->fixture = $this->getMock('\Rhubarb\ResultStore\PhpAmqp', array('declareQueue'), array($this->rhubarb), '');
        $this->fixture->setConnection($connection);
        $this->fixture->expects($this->once())
            ->method('declareQueue')
            ->will($this->returnValue($this->queue));

        $task = $this->getAsyncResultMock(
            $this->rhubarb,
            $this->getMessageMock($this->rhubarb, $this->getSignatureMock($this->rhubarb, array(), array(), array()))
        );
        return $task;
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->fixture = null;
    }

    public function testGetTaskResult()
    {
        $task = $this->mockUpFixture();
        $result = $this->fixture->getTaskResult($task);
        $this->assertEquals(4, $result->getResult());
        $this->assertNull($result->getTraceback());
        $this->assertEquals(AsyncResult::SUCCESS, $result->getState());
    }

    public function testWillHandleException()
    {
        $task = $this->mockUpFixture();
        $this->queue->expects($this->once())->method('delete')->will($this->throwException(new \AMQPChannelException));
        $this->assertNull($this->fixture->getTaskResult($task));
    }
}
 
