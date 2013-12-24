<?php
namespace Rhubarb\Message;

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
 * @category    RhubarbTests
 */
use Rhubarb\Rhubarb;
use Rhubarb\RhubarbTestCase;

/**
 * @package     Rhubarb
 * @category    RhubarbTests
 */
class MessageTest extends RhubarbTestCase
{
    /**
     * @var Message
     */
    protected $fixture;

    /**
     * @param array $brokerHeaders
     * @param array $brokerProperties
     * @param array $signatureHeaders
     * @param array $signatureProperties
     * @param array $body
     */
    protected function buildMocks($brokerHeaders = array(), $brokerProperties = array(),
                                  $signatureHeaders = array(), $signatureProperties = array(), $body = array())
    {
        $brokerMock = $this->getBrokerMock($brokerHeaders, $brokerProperties);
        $this->rhubarb = $this->getRhubarbMock($brokerMock);
        $this->signature = $this->getSignatureMock($this->rhubarb, $signatureHeaders, $signatureProperties, $body);
    }

    public function setUp()
    {
    }

    public function tearDown()
    {
        $this->fixture = null;
        $this->rhubarb = null;
        $this->signature = null;
    }

    /**
     * @group V2
     * @group Message
     */
    public function testSimpleMerge()
    {
        /* setup mocks */
        $brokerHeaders = array();
        $brokerProperties = array('content_type' => 'application/json', 'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8);
        $signatureHeaders = array('lang' => 'py', 'c_type' => 'test.task');
        $signatureProperties = array('correlation_id' => 'abcdef0123456789');
        $body = array('args' => array(1, 2), 'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'));

        $this->buildMocks($brokerHeaders, $brokerProperties, $signatureHeaders, $signatureProperties, $body);

        $this->fixture = new Message($this->rhubarb, $this->signature);

        /* expected result */
        $expected = array(
            'headers' => array_merge($brokerHeaders, $signatureHeaders),
            'properties' => array_merge($brokerProperties, $signatureProperties),
            'body' => $body
        );

        $payload = $this->fixture->getPayload();
        $this->assertEquals($expected, $payload);
    }

    /**
     * @group V2
     * @group Message
     */
    public function testBrokerAndSignatureMerge()
    {
        /* setup mocks */
        $brokerHeaders = array('lang' => 'py');
        $brokerProperties = array('content_encoding' => 'utf-8');

        $signatureHeaders = array('lang' => 'php', 'c_type' => 'test.task');
        $signatureProperties = array('correlation_id' => 'abcdef0123456789');

        $body = array('args' => array(1, 2), 'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'));

        $this->buildMocks($brokerHeaders, $brokerProperties, $signatureHeaders, $signatureProperties, $body);


        $this->fixture = new Message($this->rhubarb, $this->signature);

        /* expected result */
        $expected = array(
            'headers' => array('lang' => 'php', 'c_type' => 'test.task'),
            'properties' => array('content_encoding' => 'utf-8', 'correlation_id' => 'abcdef0123456789'),
            'body' => $body
        );

        $this->assertEquals($expected, $this->fixture->getPayload());
    }

    public function testGetSignature()
    {

    }

    public function testSetSignature()
    {

    }

    public function testToString()
    {

    }

    public function testGetPayLoad()
    {

    }

    public function testGetProperties()
    {

    }

    public function testGetBody()
    {

    }

    public function testGetHeaders()
    {

    }

    public function testSetIsSent()
    {

    }

    public function testIsSent()
    {

    }

    public function testDispatch()
    {

    }
}
 
