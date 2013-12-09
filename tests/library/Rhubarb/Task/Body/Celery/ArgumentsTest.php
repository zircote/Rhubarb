<?php
namespace Rhubarb\Task\Body\Celery;

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
 * @category    Rhubarb\Task\Body\Celery
 */
use PHPUnit_Framework_TestCase;

/**
 * @package     Rhubarb
 * @category    Rhubarb\Task\Body\Celery
 */
class ArgumentsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Arguments
     */
    protected $fixture;
    public function setUp()
    {
        $this->fixture = new Arguments('12345');
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
    public function testConstructor()
    {
        $result = $this->fixture->toArray();
        $this->assertFalse($result['terminate']);
        $this->assertNull($result['signal']);
        $this->assertEquals('12345', $result['task_id']);
    }

    /**
     * 
     */
    public function testSetsArgsFromConstructor()
    {
        $this->fixture = new Arguments('12345', true, '4321');
        $result = $this->fixture->toArray();
        $this->assertTrue($result['terminate']);
        $this->assertEquals('4321', $result['signal']);
        $this->assertEquals('12345', $result['task_id']);
    }

    /**
     * 
     */
    public function testFactory()
    {
        $this->fixture = Arguments::factory('12345', true, '4321');
        $result = $this->fixture->toArray();
        $this->assertTrue($result['terminate']);
        $this->assertEquals('4321', $result['signal']);
        $this->assertEquals('12345', $result['task_id']);
    }
}
 
