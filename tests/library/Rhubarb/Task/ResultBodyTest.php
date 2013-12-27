<?php
namespace Rhubarb\Task;

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
 * @category    Rhubarb
 */
use Rhubarb\RhubarbTestCase;

/**
 * @package     Rhubarb
 * @category    Rhubarb
 * @group Rhubarb\Task\ResultBody
 */
class ResultBodyTest extends RhubarbTestCase
{
    /**
     * @var ResultBody
     */
    protected $fixture;

    protected $body =  array(
        'state' => AsyncResult::STARTED,
        'children' => array(1,2),
        'traceback' => array('trace.me'),
        'result' => 4
    );
    
    public function setUp()
    {
        $this->fixture = new ResultBody($this->body);
    }

    public function tearDown()
    {
        $this->fixture = null;
    }

    public function testConstructor()
    {
        $this->assertEquals($this->body['state'], $this->fixture->getState());
        $this->assertEquals($this->body['children'], $this->fixture->getChildren());
        $this->assertEquals($this->body['traceback'], $this->fixture->getTraceback());
        $this->assertEquals($this->body['result'], $this->fixture->getResult());
    }

    /**
     * @expectedException \Rhubarb\Exception\RuntimeException
     * @expectedExceptionMessage status provided is not a known state
     */
    public function testInvalidStateProvided()
    {
        $this->body['state'] = 'UNKNOWN_STATE_THROW_EXCEPTION';
        new ResultBody($this->body);
    }
}
 
