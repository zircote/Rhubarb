<?php
namespace Rhubarb\Task\Body\Python;

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
 * @category    RhubarbTests\Body\Python
 */
use  PHPUnit_Framework_TestCase;
use Rhubarb\Exception\KwargException;

/**
 * @package     Rhubarb
 * @category    Task\Body\Python
 * @group Task
 * @group Body
 * @group Python
 * @group Kwarg
 */
class KwargsTest extends PHPUnit_Framework_TestCase
{

    public function testToArray()
    {
        $expected = array('arg1' => 'arg_1', 'arg2' => 'arg_2');
        $kwargs = new Kwargs($expected);
        $this->assertEquals($expected, $kwargs->toArray());
    }

    /**
     * @expectedException \Rhubarb\Exception\KwargException
     */
    public function testInvalidKwargKey()
    {
        $expected = array('1not' => 'valid');
        new Kwargs($expected);
    }

    /**
     *
     */
    public function testArrayAccess()
    {
        $kwargs = new Kwargs();
        $expected = array('assigned_arg1' => 'arg_1', 'assigned_arg2' => 'arg_2');
        $kwargs['assigned_arg1'] = 'arg_1';
        $kwargs['assigned_arg2'] = 'arg_2';
        $this->assertEquals($expected, $kwargs->toArray());
    }

    /**
     *
     */
    public function testPropertyAccess()
    {
        $kwargs = new Kwargs();
        $expected = array('assigned_arg1' => 'arg_1', 'assigned_arg2' => 'arg_2');
        $kwargs->assigned_arg1 = 'arg_1';
        $kwargs->assigned_arg2 = 'arg_2';
        $this->assertEquals($expected, $kwargs->toArray());
    }

    /**
     *
     */
    public function testPropertyAndArrayAccess()
    {
        $kwargs = new Kwargs();
        $expected = array('assigned_arg1' => 'arg_1', 'assigned_arg2' => 'arg_2');
        $kwargs['assigned_arg1'] = 'arg_1';
        $kwargs->assigned_arg2 = 'arg_2';
        $this->assertEquals($expected, $kwargs->toArray());
    }
}
 
