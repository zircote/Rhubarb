<?php
namespace Rhubarb\Task\Body;

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
 * @category    RhubarbTests\Body
 */
use Rhubarb\Rhubarb;
use Rhubarb\RhubarbTestCase;

/**
 * @package     Rhubarb
 * @category    RhubarbTests\Body
 * 
 * @group \Rhubarb\
 * @group \Rhubarb\Task
 * @group \Rhubarb\Task\Body
 * @group \Rhubarb\Task\Body\Python
 */
class PythonTest extends RhubarbTestCase
{
    /**
     * @var Python
     */
    protected $fixture;

    public function setUp()
    {
        $this->fixture = new Python(array(1,1), $this->getKwargsMock(array('arg1' => 'arg_1', 'arg2' => 'arg_2')));
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->fixture = null;
    }

    /**
     *
     */
    public function testSerialize()
    {
        $this->fixture->setArgs(array(1, 2));
        $actual = $this->fixture->serialize();
        $expected = json_encode(
            array('args' => array(1, 2), 'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')),
            Rhubarb::$jsonOptions
        );
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    /**
     *
     */
    public function testToArray()
    {
        $this->fixture->setArgs(array(1, 2));
        $actual = $this->fixture->toArray();
        $expected = array('args' => array(1, 2), 'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'));
        $this->assertEquals($expected, $actual);
    }

    /**
     *
     */
    public function testToString()
    {
        $this->fixture->setArgs(array(1, 2));
        $actual = $this->fixture->__toString();
        $expected = json_encode(
            array('args' => array(1, 2), 'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')),
            Rhubarb::$jsonOptions
        );
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }
    
    public function testArgsandKwargs()
    {
        $expected = array(2,2);
        $this->fixture->setArgs($expected);
        $this->assertEquals($expected, $this->fixture->getArgs());
        $expected = array('t' => 'z', 'c' => 'b');
        $this->fixture->setKwargs($expected);
        $this->assertEquals($expected, $this->fixture->getKwargs());
    }
    public function testGetHeaders()
    {
        $expected = array('lang' => 'py');
        $this->assertEquals($expected, $this->fixture->getHeaders());
    }
}
 
