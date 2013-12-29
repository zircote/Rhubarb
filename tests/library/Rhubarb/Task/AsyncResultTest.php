<?php
namespace Rhubarb\Task;

/**
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
 * @category    Tests
 * @subcategory AsyncResult
 */
use AMQP\Message;
use Rhubarb\RhubarbTestCase;

/**
 * @package     Rhubarb
 * @category    Tests
 * @subcategory AsyncResult
 *
 * @group \Rhubarb
 * @group \Rhubarb\Task
 * @group \Rhubarb\Task\AsyncResult
 */
class AsyncResultTest extends RhubarbTestCase
{
    /**
     * @var AsyncResult|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fixture;
    /**
     * @var \Rhubarb\Task\Message|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $message;
    /**
     * @var \Rhubarb\ResultStore\ResultStoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultStore;

    protected $parameters = array(
        'name' => 'test.task',
        'id' => '1234567890abcdef'
    );

    /**
     * @param ResultBody $body
     */
    protected function getResultMock(ResultBody $body)
    {
        $this->resultStore->expects($this->atLeastOnce())
            ->method('getTaskResult')
            ->will($this->returnValue($body));
    }

    protected function setUp()
    {
        $broker = $this->getBrokerMock();
        $this->resultStore = $this->getMock('\Rhubarb\ResultStore\ResultStoreInterface', array('getTaskResult'));
        $this->rhubarb = $this->getRhubarbMock($broker, $this->resultStore);
        $this->message = $this->getMock('\\Rhubarb\\Task\\Message', array('getId'), array(), '', false);
        $this->fixture = new AsyncResult($this->rhubarb, $this->message);
    }

    public function testRevoke()
    {
        //{"destination": null, "method": "revoke", "arguments": {"signal": null, "terminate": false, "task_id": "c72e99dd-13aa-4791-8b21-6ae94594972b"}}
    }

    /**
     *
     */
    public function testGetId()
    {

        $this->message->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($this->parameters['id']));

        $this->assertEquals($this->parameters['id'], $this->fixture->getId());
    }

    public function testIsReady()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::SUCCESS, 'args' => '1')));
        $this->assertTrue($this->fixture->isReady());
    }

    public function testIsReadyPending()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::PENDING, 'args' => '1')));
        $this->assertFalse($this->fixture->isReady());
    }

    public function testIsStarted()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::STARTED, 'args' => '1')));
        $this->assertTrue($this->fixture->isStarted());
    }

    public function testIsStartedRevoked()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::REVOKED, 'args' => '1')));
        $this->assertFalse($this->fixture->isStarted());
    }

    public function testIsRevoked()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::REVOKED, 'args' => '1')));
        $this->assertTrue($this->fixture->isRevoked());
    }

    public function testIsRevokedPending()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::PENDING, 'args' => '1')));
        $this->assertFalse($this->fixture->isRevoked());
    }

    public function testIsSuccess()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::SUCCESS, 'args' => '1')));
        $this->assertTrue($this->fixture->isSuccess());
    }

    public function testIsSuccessFailure()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::FAILURE, 'args' => '1')));
        $this->assertFalse($this->fixture->isSuccess());
    }

    public function testIsRetry()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::RETRY, 'args' => '1')));
        $this->assertTrue($this->fixture->isRetry());
    }

    public function testIsRetryPending()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::PENDING, 'args' => '1')));
        $this->assertFalse($this->fixture->isRetry());
    }

    public function testIsPending()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::PENDING, 'args' => '1')));
        $this->assertTrue($this->fixture->isPending());
    }

    public function testIsPendingStarted()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::STARTED, 'args' => '1')));
        $this->assertFalse($this->fixture->isPending());
    }

    public function testIsFailure()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::FAILURE, 'args' => '1')));
        $this->assertTrue($this->fixture->isFailure());
    }

    public function testIsFailureSuccess()
    {
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::SUCCESS, 'args' => '1')));
        $this->assertFalse($this->fixture->isFailure());
    }

    public function testGet()
    {
        $expected = 1;
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::SUCCESS, 'result' => $expected)));
        $actual = $this->fixture->get();
        $this->assertEquals($expected, $actual);

    }

    public function testGetSlowResult()
    {
        $this->message->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($this->parameters['id']));
        $this->setExpectedException(
            '\Rhubarb\Exception\TimeoutException',
            sprintf('AsyncResult( %s ) did not return after 1 seconds', $this->parameters['id'])
        );
        $this->getResultMock(new ResultBody(array('state' => AsyncResult::PENDING, 'result' => '')));
        $this->fixture->get(1);

    }
}
