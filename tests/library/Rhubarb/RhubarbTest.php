<?php
namespace Rhubarb;

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

/**
 * @package     Rhubarb
 * @category    Tests
 * @subcategory Rhubarb
 * @group Rhubarb
 * @group Rhubarb\Rhubarb
 */
class RhubarbTest extends RhubarbTestCase
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
            'tasks' => array(
                array(
                    'name' => 'app.add', // c_type
                    'headers' => array(
                        'timelimit' => array(30, 90)
                    )
                ),
            )
        );
        $this->rhubarb = new Rhubarb($options);
        $brokerMock = $this->getBrokerMock(array(), array());
        $resultStoreMock = $this->getResultStoreMock();
        $this->rhubarb->setBroker($brokerMock);
        $this->rhubarb->setResultStore($resultStoreMock);

    }

    /**
     *
     */
    public function testTask()
    {
        $expected = 'app.add';
        $asyncResult = $this->rhubarb->task($expected, null, array('content_encoding' => 'base64'));
        $this->assertInstanceOf('\Rhubarb\Task\Signature', $asyncResult);
        $this->assertEquals($expected, $asyncResult->getName());
        $this->assertEquals('base64', $asyncResult->getProperty('content_encoding'));
        $this->assertEquals(array(30, 90), $asyncResult->getHeader('timelimit'));
    }

    /**
     *
     */
    public function testT()
    {
        $expected = 'app.add';
        $asyncResult = $this->rhubarb->t($expected);
        $this->assertInstanceOf('\Rhubarb\Task\Signature', $asyncResult);
        $this->assertEquals($expected, $asyncResult->getName());
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
     * @group logger
     */
    public function testLogging()
    {
        $logger = $this->rhubarb->getLogger();
        $this->assertEquals('Rhubarb', $logger->getName());
    }

    public function testSetOptions()
    {

    }

    public function testGetOptions()
    {

    }

    public function testGetOption()
    {

    }

    public function testSetOption()
    {

    }

    public function testSetTasks()
    {

    }

    public function testAddTask()
    {

    }

    public function testGetTask()
    {

    }

    public function testDelTask()
    {

    }

    public function testDispatch()
    {

    }

    public function testDecode()
    {
        $encodedBody = 'eyJkZXN0aW5hdGlvbiI6IG51bGwsICJtZXRob2QiOiAicmV2b2tlIiwgImFyZ3VtZW50cyI6IHsic2lnbmFsIjogbnVsbCwgInRlcm1pbmF0ZSI6IGZhbHNlLCAidGFza19pZCI6ICJjNzJlOTlkZC0xM2FhLTQ3OTEtOGIyMS02YWU5NDU5NDk3MmIifX0=';
        $actual = $this->rhubarb->decode($encodedBody, Rhubarb::CONTENT_ENCODING_BASE64);
        $expected = '{"destination": null, "method": "revoke", "arguments": {"signal": null, "terminate": false, "task_id": "c72e99dd-13aa-4791-8b21-6ae94594972b"}}';
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testUnserialize()
    {
        $json = '{"test": "win"}';
        $expected = array('test' => 'win');
        $actual = $this->rhubarb->unserialize($json, Rhubarb::CONTENT_TYPE_JSON);
        $this->assertEquals($expected, $actual);
    }

    public function testSerialize()
    {
        $expected = '{"test":"win"}';
        $json = array('test' => 'win');
        $actual = $this->rhubarb->serialize($json, Rhubarb::CONTENT_TYPE_JSON);
        $this->assertEquals($expected, $actual);
    }

    public function testEncode()
    {
        $body = '{"destination": null, "method": "revoke", "arguments": {"signal": null, "terminate": false, "task_id": "c72e99dd-13aa-4791-8b21-6ae94594972b"}}';
        $expected = 'eyJkZXN0aW5hdGlvbiI6IG51bGwsICJtZXRob2QiOiAicmV2b2tlIiwgImFyZ3VtZW50cyI6IHsic2lnbmFsIjogbnVsbCwgInRlcm1pbmF0ZSI6IGZhbHNlLCAidGFza19pZCI6ICJjNzJlOTlkZC0xM2FhLTQ3OTEtOGIyMS02YWU5NDU5NDk3MmIifX0=';
        $actual = $this->rhubarb->encode($body, Rhubarb::CONTENT_ENCODING_BASE64);
        $this->assertEquals($expected, $actual);
    }
}
