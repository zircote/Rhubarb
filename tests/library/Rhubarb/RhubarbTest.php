<?php
namespace RhubarbTests;

    /**
     * @license http://www.apache.org/licenses/LICENSE-2.0
     * Copyright [2012] [Robert Allen]
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
     * @subcategory Task
     */

/**
 * @package     Rhubarb
 * @category    Tests
 * @subcategory Rhubarb
 */
class RhubarbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rhubarb\Broker\Test
     */
    protected $broker;
    /**
     * @var \Rhubarb\ResultStore\Test
     */
    protected $resultStore;
    /**
     * @var \Rhubarb\Rhubarb
     */
    protected $rhubarb;

    /**
     *
     */
    public function setup()
    {
        $options = array(
            'broker' => array(
                'type' => 'Test'
            ),
            'result_store' => array(
                'type' => 'Test'
            ),
            'logger' => array(
                'loggers' => array(
                    'Rhubarb' => array(
                        'level' => 'ERROR',
                        'appenders' => array('Rhubarb'),
                    ),
                ),
                'appenders' => array(
                    'Rhubarb' => array(
                        'class' => 'LoggerAppenderConsole',
                        'layout' => array(
                            'class' => 'LoggerLayoutSimple'
                        )
                    )
                )
            )
        );
        $this->rhubarb = new \Rhubarb\Rhubarb($options);
        /* @var \Rhubarb\Broker\Test $broker */
        $this->broker = $this->rhubarb->getBroker();
        $this->resultStore = $this->rhubarb->getResultStore();
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->rhubarb = null;
        $this->broker = null;
        $this->resultStore = null;
    }

    /**
     * @param string $status
     * @param        $taskId
     *
     * @return string
     */
    protected function getSuccesfulResult($status = 'SUCCESS', $taskId)
    {
        $resultExpected = sprintf(
            '{"status": "%s", "traceback": null, "result": 2105, "task_id": "%s", "children": []}',
            $status,
            $taskId
        );
        return $resultExpected;
    }

    /**
     *
     */
    public function testRhubarb()
    {
        $this->resultStore->setNextResult(false);
        $task = $this->rhubarb->sendTask('test.task', array(2, 2));
        $task->delay();
        $expectedArgs = sprintf('{"id":"%s","task":"test.task","args":[2,2],"kwargs":{},"expires":null,"utc":true,' .
            '"callbacks":null,"eta":null,"errbacks":null}', $task->getId());
        $expected = $this->getSuccesfulResult('SUCCESS', $task->getId());
        $this->resultStore->setNextResult($expected);
        $this->assertEquals($expectedArgs, $this->broker->getPublishedValues());
        $this->assertEquals(2105, $task->get());
        $this->assertEquals('SUCCESS', $task->state());
        $this->assertNull($task->traceback());
        $this->assertFalse($task->failed());
        $this->assertTrue($task->successful());
    }

    public function testKwargsArePassed()
    {

        $this->resultStore->setNextResult(false);
        $res = $this->rhubarb->sendTask('test.task', array('arg1' => 2, 'arg2' => 2));
        $expected = sprintf(
            '{"id":"%s","task":"test.task","args":[],"kwargs":{"arg1":2,"arg2":2},"expires":null,"utc":true,' .
            '"callbacks":null,"eta":null,"errbacks":null}',
            $res->getId()
        );
        $res->delay();
        $this->assertEquals($expected, $this->broker->getPublishedValues());
    }

    /**
     * @expectedException \Rhubarb\Exception\TimeoutException
     */
    public function testTimeout()
    {
        $this->resultStore->setWait(4);
        $res = $this->rhubarb->sendTask('test.task', array(2, 2));
        $res->delay();
        $this->resultStore->setNextResult($this->getSuccesfulResult('SUCCESS', $res->getId()));
        $res->get(1);
    }

    /**
     *
     */
    public function testTimeWaits()
    {
        $this->resultStore->setWait(4);
        $task = $this->rhubarb->sendTask('test.task', array(2, 2));
        $this->resultStore->setNextResult($this->getSuccesfulResult('SUCCESS', $task->getId()));
        $task->delay();
        $this->assertEquals(2105, $task->get());
    }

    /**
     * @group logger
     */
    public function testLogging()
    {
        $logger = $this->rhubarb->getLogger();
        $this->assertEquals('Rhubarb', $logger->getName());
    }
}
