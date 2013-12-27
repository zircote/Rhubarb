<?php
namespace Rhubarb\Task\Body;

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
use PHPUnit_Framework_TestCase;
use Rhubarb\Rhubarb;

/**
 * @package     Rhubarb
 * @category    Rhubarb
 */
class CeleryTest extends PHPUnit_Framework_TestCase
{

    protected $expectedArguments = array('task_id' => '54321', 'terminate' => false, 'signal' => null);
    protected $expected = array();
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Task\Body\Celery
     */
    protected $fixture;

    protected function setUp()
    {

    }

    protected function setupMock()
    {
        $this->expected = array('destination' => 'destination', 'method' => 'revoke', 'arguments' => $this->expectedArguments);
        /* @var \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Task\Body\Celery\Arguments $mock */
        $mock = $this->getMock('\Rhubarb\Task\Body\Celery\Arguments', array('toArray'), array(), '', false);
        $mock->expects($this->once())->method('toArray')->will($this->returnValue($this->expectedArguments));
        $this->fixture = new Celery('revoke', $mock, 'destination');
    }

    /**
     *
     */
    protected function tearDown()
    {
        $this->fixture = null;
    }

    /**
     *
     */
    public function testConstructor()
    {
        $this->setupMock();
        $this->assertEquals($this->expected, $this->fixture->toArray());
    }

    public function testGetHeaders()
    {
        $this->fixture = null;
        /* @var \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Task\Body\Celery\Arguments $mock */
        $mock = $this->getMock('\Rhubarb\Task\Body\Celery\Arguments', array(), array(), '', false);
        $this->fixture = new Celery('revoke', $mock, 'destination');
        $this->assertEmpty($this->fixture->getHeaders());

    }

    public function testSerialize()
    {
        $this->setupMock();
        $expected = json_encode($this->expected, Rhubarb::$jsonOptions);
        $this->assertJsonStringEqualsJsonString($expected, $this->fixture->serialize());
    }

    /**
     *
     */
    public function testToString()
    {
        $this->setupMock();
        $expected = json_encode($this->expected, Rhubarb::$jsonOptions);
        $this->assertJsonStringEqualsJsonString($expected, (string)$this->fixture);
    }

}
 
