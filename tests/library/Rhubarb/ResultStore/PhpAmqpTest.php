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

/**
 * @package     Rhubarb
 * @category    RhubarbTests\Result
 */
class PhpAmqpTest extends RhubarbTestCase
{

    /**
     * @var PhpAmqp
     */
    protected $fixture;

    public function setUp()
    {
        $this->rhubarb = $this->getRhubarbMock($this->getBrokerMock(array(), array()));
        $this->fixture = new PhpAmqp($this->rhubarb);
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
        $this->markTestIncomplete();
    }

}
 
