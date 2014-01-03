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
 * application/vnd.celery.v1+json
 * application/vnd.celery.v2+json
 * @package     Rhubarb
 * @category    RhubarbTests
 */
use Rhubarb\Rhubarb;
use Rhubarb\RhubarbTestCase;

/**
 * @package     Rhubarb
 * @category    RhubarbTests
 * @group Rhubarb
 * @group Rhubarb\Task
 * @group Rhubarb\Task\Message
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
     * @group Task
     */
    public function testSimpleMerge()
    {
        /* setup mocks */
        $brokerHeaders = array();
        $brokerProperties = array('content_type' => 'application/json', 'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8);
        $signatureHeaders = array('lang' => 'py', 'c_type' => 'test.task', 'timelimit' => array(null, null));
        $signatureProperties = array('correlation_id' => 'abcdef0123456789');
        $body = array('args' => array(1, 2), 'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'));

        $this->buildMocks($brokerHeaders, $brokerProperties, $signatureHeaders, $signatureProperties, $body);

        $this->fixture = new Message($this->rhubarb, $this->signature);

        /* expected result */
        $expected = array(
            'headers' => array_merge($brokerHeaders, $signatureHeaders),
            'properties' => array_merge($brokerProperties, $signatureProperties),
            'args' => $body,
            'sent_event' => array(
                'uuid' => 'abcdef0123456789',
                'name' => 'test.task',
                'args' => $body['args'],
                'kwargs' => $body['kwargs'],
                'retries' => null,
                'eta' => null,
                'expires' => null
            )
        );

        $this->fixture->setMessageFormat(Message::V2);
        $payload = $this->fixture->getPayload();
        $this->assertEquals($expected, $payload);
    }

    /**
     * @group V2
     * @group Task
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
            'headers' => array('lang' => 'php', 'c_type' => 'test.task', 'timelimit' => array(null, null)),
            'properties' => array('content_encoding' => 'utf-8', 'correlation_id' => 'abcdef0123456789'),
            'args' => $body,
            'sent_event' => array(
                'uuid' => 'abcdef0123456789',
                'name' => 'test.task',
                'args' => $body['args'],
                'kwargs' => $body['kwargs'],
                'retries' => null,
                'eta' => null,
                'expires' => null
            )
        );

        $this->fixture->setMessageFormat(Message::V2);
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
        $eta = $expectedETA->format(\DateTime::ISO8601);
        $expected = array(
            'headers' => array(
                'lang' => 'py',
                'c_type' => 'test.task',
                'timelimit' => array(null, null),
                'eta' => $eta,
            ),
            'properties' => array(
                'content_type' => Rhubarb::CONTENT_TYPE_JSON,
                'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
                'correlation_id' => 'abcdef0123456789'
            ),
            'args' => array(
                'args' => array(1, 2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')
            ),
            'sent_event' => array(
                'uuid' => 'abcdef0123456789',
                'name' => 'test.task',
                'args' => array(1,2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'),
                'retries' => null,
                'eta' => $eta,
                'expires' => null
            )
        );
        $this->fixture->setHeader('countdown', 60);
        $this->fixture->setMessageFormat(Message::V2);
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
            'headers' => array(
                'lang' => 'py',
                'c_type' => 'test.task',
                'eta' => $eta,
                'timelimit' => array(null, null)
            ),
            'properties' => array(
                'content_type' => Rhubarb::CONTENT_TYPE_JSON,
                'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
                'correlation_id' => 'abcdef0123456789'
            ),
            'args' => array(
                'args' => array(1, 2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')
            ),
            'sent_event' => array(
                'uuid' => 'abcdef0123456789',
                'name' => 'test.task',
                'args' => array(1,2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'),
                'retries' => null,
                'eta' => $eta,
                'expires' => null
            )
        );
        $this->fixture->setHeader('eta', $eta);
        $this->fixture->setMessageFormat(Message::V2);
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
            'headers' => array(
                'lang' => 'py',
                'c_type' => 'test.task',
                'timelimit' => array(null, null),
                'expires' => $expires,
            ),
            'properties' => array(
                'content_type' => Rhubarb::CONTENT_TYPE_JSON,
                'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
                'correlation_id' => 'abcdef0123456789'
            ),
            'args' => array(
                'args' => array(1, 2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')
            ),
            'sent_event' => array(
                'uuid' => 'abcdef0123456789',
                'name' => 'test.task',
                'args' => array(1,2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'),
                'retries' => null,
                'eta' => null,
                'expires' => $expires
            )
        );
        $this->fixture->setHeader('expires', $expires);
        $this->fixture->setMessageFormat(Message::V2);
        $actual = $this->fixture->getPayload();
        $this->assertEquals($expected, $actual);

    }

    public function testGetPayLoadV1()
    {
        $this->buildSimpleMock();
        $this->fixture->setMessageFormat(Message::V1);
        $expected = array(
            'properties' => array(
                'content_type' => Rhubarb::CONTENT_TYPE_JSON,
                'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
                'correlation_id' => 'abcdef0123456789'
            ),
            'headers' => array(),
            'args' => array(
                'task' => 'test.task',
                'id' => 'abcdef0123456789',
                'args' => array(1, 2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'),
                'retries' => null,
                'eta' => null,
                'expire' => null,
                'utc' => true,
                'callbacks' => null,
                'errbacks' => null,
                'timelimit' => array(null, null),
                'taskset' => array(),
                'chord' => array()
            ),
            'sent_event' => array(
                'uuid' => 'abcdef0123456789',
                'name' => 'test.task',
                'args' => array(1,2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'),
                'retries' => null,
                'eta' => null,
                'expires' => null
            )
        );
        $actual = $this->fixture->getPayload();
        $this->assertEquals($expected, $actual);

    }

    public function testGetPayLoadV2()
    {
        $this->buildSimpleMock();
        $this->fixture->setMessageFormat(Message::V2);
        $expected = array(
            'headers' => array(
                'lang' => 'py',
                'c_type' => 'test.task',
                'timelimit' => array(null, null)
            ),
            'properties' => array(
                'content_type' => Rhubarb::CONTENT_TYPE_JSON,
                'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
                'correlation_id' => 'abcdef0123456789'
            ),
            'args' => array(
                'args' => array(1, 2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2')
            ),
            'sent_event' => array(
                'uuid' => 'abcdef0123456789',
                'name' => 'test.task',
                'args' => array(1,2),
                'kwargs' => array('arg1' => 'arg_1', 'arg2' => 'arg_2'),
                'retries' => null,
                'eta' => null,
                'expires' => null
            )
        );
        $actual = $this->fixture->getPayload();
        $this->assertEquals($expected, $actual);

    }

    /**
     * @expectedException \Rhubarb\Exception\MessageSentException
     * @expectedExceptionMessage message sent, setting properties is not allowed [ Rhubarb\Task\Message::setProperty(message_property::true) ]
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
        $expected = array(
            'lang' => 'py',
            'c_type' => 'test.task',
            'c_meth' => null,
            'c_shadow' => null,
            'eta' => null,
            'expires' => null,
            'callbacks' => null,
            'errbacks' => null,
            'chain' => array(),
            'group' => array(),
            'chord' => array(),
            'retries' => null,
            'timelimit' => array(null, null)
        );
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
 