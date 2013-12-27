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
 * @group Rhubarb
 * @group Rhubarb\Message
 * @group Rhubarb\Message\Message
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
     *
     */
    protected function buildSimpleMock()
    {
        $brokerHeaders = array();
        $brokerProperties = array('content_type' => 'application/json', 'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8);
        $signatureHeaders = array('lang' => 'py', 'c_type' => 'test.task');
        $signatureProperties = array('correlation_id' => 'abcdef0123456789');
        $body = array('args' => array(1, 2), 'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'));

        $this->buildMocks($brokerHeaders, $brokerProperties, $signatureHeaders, $signatureProperties, $body);

        $this->fixture = new Message($this->rhubarb, $this->signature);

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

    public function testToString()
    {
        $this->buildSimpleMock();
        $this->assertTrue(is_string((string)$this->fixture));
    }

    public function testGetPayLoadWithCountdown()
    {
        $this->buildSimpleMock();
        $expectedETA = new \DateTime();
        
        $expectedETA->add(new \DateInterval("PT60S"));
        $expected = array(
            'headers' => array('lang' => 'py', 'c_type' => 'test.task',
                'eta' => $expectedETA->format(\DateTime::ISO8601)),
            'properties' => array(
                'content_type' => Rhubarb::CONTENT_TYPE_JSON,
                'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
                'correlation_id' => 'abcdef0123456789'
            ),
            'body' => array(
                'args' => array(1, 2), 
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')
            )
        );
        $this->fixture->setHeader('countdown', 60);
        $actual = $this->fixture->getPayload();
        $this->assertEquals($expected, $actual);

    }

    public function testGetPayLoadWithETA()
    {
        $this->buildSimpleMock();
        $expectedETA = new \DateTime();
        
        $expectedETA->add(new \DateInterval("PT60S"));
        $eta = $expectedETA->format(\DateTime::ISO8601);
        $expected = array(
            'headers' => array('lang' => 'py', 'c_type' => 'test.task', 'eta' => $eta,
            ),
            'properties' => array(
                'content_type' => Rhubarb::CONTENT_TYPE_JSON,
                'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
                'correlation_id' => 'abcdef0123456789'
            ),
            'body' => array(
                'args' => array(1, 2), 
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')
            )
        );
        $this->fixture->setHeader('eta', $eta);
        $actual = $this->fixture->getPayload();
        $this->assertEquals($expected, $actual);

    }

    public function testGetPayLoadWithExpires()
    {
        $this->buildSimpleMock();
        $expectedETA = new \DateTime();
        
        $expectedETA->add(new \DateInterval("PT60S"));
        $expires = $expectedETA->format(\DateTime::ISO8601);
        $expected = array(
            'headers' => array('lang' => 'py', 'c_type' => 'test.task',
                'expires' => $expires,
            ),
            'properties' => array(
                'content_type' => Rhubarb::CONTENT_TYPE_JSON,
                'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
                'correlation_id' => 'abcdef0123456789'
            ),
            'body' => array(
                'args' => array(1, 2), 
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')
            )
        );
        $this->fixture->setHeader('expires', $expires);
        $actual = $this->fixture->getPayload();
        $this->assertEquals($expected, $actual);

    }

    public function testGetPayLoad()
    {
        $this->buildSimpleMock();
        $expected = array(
            'headers' => array('lang' => 'py', 'c_type' => 'test.task'),
            'properties' => array(
                'content_type' => Rhubarb::CONTENT_TYPE_JSON,
                'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
                'correlation_id' => 'abcdef0123456789'
            ),
            'body' => array(
                'args' => array(1, 2), 
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')
            )
        );
        $actual = $this->fixture->getPayload();
        $this->assertEquals($expected, $actual);

    }

    /**
     * @expectedException \Rhubarb\Exception\MessageSentException
     * @expectedExceptionMessage message sent, setting properties is not allowed [ Rhubarb\Message\Message::setProperty(message_property::true) ]
     */
    public function testSetPropertyAfterMessageSent()
    {
        $this->buildSimpleMock();
        $this->fixture->getProperties();
        $this->fixture->getHeaders();
        $this->fixture->getBody();
        $this->fixture->setIsSent();
        $this->fixture->setProperty('message_property', 'true');
    }

    public function testGetHeaders()
    {
        $this->buildSimpleMock();
        $expected = array('lang' => 'py', 'c_type' => 'test.task');
        $actual = $this->fixture->getHeaders();
        $this->assertEquals($expected, $actual);
        $this->fixture->getPayload();

    }
    public function testGetHeader()
    {
        
        $this->buildSimpleMock();
        $expected = 'py';
        $actual = $this->fixture->getHeader('lang');
        $this->assertEquals($expected, $actual);
        $this->fixture->getPayload();
    }
}
 
