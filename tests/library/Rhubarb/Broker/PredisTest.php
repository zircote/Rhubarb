<?php
namespace Rhubarb\Broker;

/**
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
 * @category    Tests
 * @subcategory AsyncResult
 */
use Rhubarb\Message\Message;
use Rhubarb\PhpPredisTestCase;

/**
 * @package     Rhubarb
 * @category    Tests
 * @subcategory Rhubarb
 * @group Redis
 * @group Rhubarb\Broker\Predis
 */
class PredisTest extends PhpPredisTestCase
{
    /**
     * @var Predis
     */
    protected $fixture;

    public function setUp()
    {
        $this->rhubarb = $this->getRhubarbMock($this->getBrokerMock(array(),$this->getResultStoreMock(), array()));
        $this->fixture = new Predis($this->rhubarb);
    }

    /**
     * 
     */
    public function tearDown()
    {
        $this->rhubarb = null;
        $this->fixture = null;
    }

    /**
     * 
     */
    public function testConstructor()
    {
        $this->assertInstanceOf('\Rhubarb\Rhubarb', $this->fixture->getRhubarb());
        $this->assertInstanceOf('\Predis\Client', $this->fixture->getConnection());
    }

    /**
     * 
     */
    public function testPublishTask()
    {
        $pmock = $this->setPredisMethod(
            $this->getPredisClientMock(false),
            'lpush',
            $this->returnValue(true),
            $this->exactly(1)
        );
        $this->fixture->setConnection($pmock);

        $message = new Message(
            $this->rhubarb,
            $this->getSignatureMock($this->rhubarb, array(), array(), $this->getBodyMock(array(2,1)))
        );
        $this->fixture->publishTask($message);
    }

}
