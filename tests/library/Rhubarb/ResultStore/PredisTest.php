<?php
namespace Rhubarb\ResultStore;

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
 * @category    RhubarbTests\Result
 */
use Rhubarb\RhubarbTestCase;
use Rhubarb\Task\AsyncResult;

/**
 * @package     Rhubarb
 * @category    RhubarbTests\Result
 */
class PredisTest extends RhubarbTestCase
{

    /**
     * @var Predis
     */
    protected $fixture;

    public function getMockUp()
    {
        $this->rhubarb = $this->getRhubarbMock($this->getBrokerMock(array(), array()));
        $this->fixture = new Predis($this->rhubarb);
        $result = '{ "state": "SUCCESS", "traceback": null, "result": 4, "children": [] }';
        $connection = $this->getMock('\Predis\Client', array('get'), array(), '', false);
        $connection->expects($this->once())->method('get')->will($this->returnValue($result));
        $this->fixture->setConnection($connection);
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
        $this->rhubarb = null;
        $this->fixture = null;
    }

    
    public function testGetTaskResult()
    {
        $task = $this->getMockUp();
        
        $result = $this->fixture->getTaskResult($task);
        $this->assertEquals(4, $result->getResult());
        $this->assertNull($result->getTraceback());
        $this->assertEquals(AsyncResult::SUCCESS, $result->getState());
    }

}
 
