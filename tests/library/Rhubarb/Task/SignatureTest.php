<?php
namespace Rhubarb;

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
use Rhubarb\Task\Signature;
use stdClass;

/**
 * @package     Rhubarb
 * @category    RhubarbTests
 * @group Task
 * @group Signature
 */
class SignatureTest extends RhubarbTestCase
{
    /**
     * @var Signature
     */
    protected $fixture;
    /**
     * @var array
     */
    protected $fixtureArgs = array(
        'body' => array(1, 2),
        'name' => __CLASS__,
        'headers' => array('lang' => 'py', 'c_meth' => 'my_meth'),
        'properties' => array('content_encoding' => 'UTF-8')
    );

    /**
     *
     */
    public function setup()
    {
        $brokerMock = $this->getBrokerMock(array(), array());
        $this->rhubarb = $this->getRhubarbMock($brokerMock);
        $this->fixture = new Signature(
            $this->rhubarb,
            $this->fixtureArgs['name'],
            $this->getBodyMock($this->fixtureArgs['body']),
            $this->fixtureArgs['properties'],
            $this->fixtureArgs['headers']
        );
    }

    public function testGetLang()
    {
        $this->fixture->applyAsync();
        $this->assertEquals('py', $this->fixture->getHeader('lang'));
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
        $this->assertInstanceOf('\Rhubarb\Rhubarb', $this->fixture->getRhubarb());
        $this->assertEquals($this->fixtureArgs['name'], $this->fixture->getName());
        $this->assertInstanceOf('\Rhubarb\Task\Body\BodyInterface', $this->fixture->getBody());
        $this->assertEquals($this->fixtureArgs['headers'], $this->fixture->getHeaders());
        $this->assertEquals($this->fixtureArgs['properties'], $this->fixture->getProperties());
    }

    /**
     *
     */
    public function testSetBody()
    {
        $expected = array(2, 1);
        $bodyMock = $this->getBodyMock($expected);
        $this->fixture->setBody($bodyMock);
        $this->assertEquals($expected, $this->fixture->getBody()->toArray());

    }

    /**
     *
     */
    public function testGetBody()
    {
        $this->assertEquals($this->fixtureArgs['body'], $this->fixture->getBody()->toArray());
    }

    /**
     *
     */
    public function testSetHeader()
    {
        $additive = array('lang' => 'py', 'c_type' => 'my_type');
        $expected = array_merge($this->fixtureArgs['headers'], $additive);
        $this->fixture->setHeader('c_type', 'my_type');
        $this->assertEquals($expected, $this->fixture->getHeaders());
    }

    public function testGetHeader()
    {
        $this->assertEquals($this->fixtureArgs['headers']['c_meth'], $this->fixture->getHeader('c_meth'));
    }

    /**
     *
     */
    public function testSetHeaders()
    {
        $expected = array('lang' => 'py', 'c_type' => 'my_type');
        $this->fixture->setHeaders($expected);
        $this->assertEquals($expected, $this->fixture->getHeaders());
    }

    /**
     *
     */
    public function testGetHeaders()
    {
        $this->assertEquals($this->fixtureArgs['headers'], $this->fixture->getHeaders());
    }

    /**
     *
     */
    public function testSetProperty()
    {
        $additive = array('content_type' => 'application/json');
        $expected = array_merge($this->fixtureArgs['properties'], $additive);
        $this->fixture->setProperty(key($additive), current($additive));
        $this->assertEquals($expected, $this->fixture->getProperties());
    }

    /**
     *
     */
    public function testGetProperty()
    {
        $this->assertEquals(
            $this->fixtureArgs['properties']['content_encoding'],
            $this->fixture->getProperty('content_encoding')
        );
    }

    /**
     *
     */
    public function testSetProperties()
    {
        $additive = array('content_type' => 'application/json');
        $expected = array_merge($this->fixtureArgs['properties'], $additive);
        $this->fixture->setProperties($expected);
        $this->assertEquals($expected, $this->fixture->getProperties());
    }

    /**
     *
     */
    public function testGetProperties()
    {
        $this->assertEquals($this->fixtureArgs['properties'], $this->fixture->getProperties());
    }

    /**
     *
     */
    public function testOnSuccess()
    {
        $this->markTestIncomplete(sprintf('%s is not complete', __METHOD__));
    }

    /**
     *
     */
    public function testOnFailure()
    {
        $this->markTestIncomplete(sprintf('%s is not complete', __METHOD__));
    }

    /**
     *
     */
    public function testOnRetry()
    {
        $this->markTestIncomplete(sprintf('%s is not complete', __METHOD__));
    }

    /**
     *
     */
    public function testFreeze()
    {
        $this->assertFalse($this->fixture->isFroze());
        $this->fixture->freeze();
        $this->assertTrue($this->fixture->isFroze());
        $this->assertRegExp(
            '#([a-zA-Z0-9]{8})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{12})#',
            $this->fixture->getId()
        );
    }

    /**
     *
     */
    public function testS()
    {
        $expected = '\Rhubarb\Task\Signature';
        $actual = $this->fixture->s();
        $this->assertInstanceOf($expected, $actual);
        $this->assertTrue($this->fixture->isMutable());
    }

    /**
     *
     */
    public function testSi()
    {
        $expected = '\Rhubarb\Task\Signature';
        $actual = $this->fixture->si();
        $this->assertInstanceOf($expected, $actual);
        $this->assertFalse($this->fixture->isMutable());
    }

    /**
     *
     */
    public function testApplyAsync()
    {
        $expected = '\Rhubarb\Task\AsyncResult';

        $messageMock = $this->getMessageMock($this->rhubarb, $this->fixture);
        $asyncMock = $this->getAsyncResultMock($this->rhubarb, $messageMock);

        $this->rhubarb->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue($asyncMock));

        $actual = $this->fixture->applyAsync();
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     *
     */
    public function testDelay()
    {
        $expected = '\Rhubarb\Task\AsyncResult';
        $messageMock = $this->getMessageMock($this->rhubarb, $this->fixture);
        $asyncMock = $this->getAsyncResultMock($this->rhubarb, $messageMock);

        $this->rhubarb->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue($asyncMock));

        $actual = $this->fixture->delay();
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     *
     */
    public function testMap()
    {
        $this->markTestSkipped('Signature::map is not implemented yet');
    }

    /**
     *
     */
    public function testStarmap()
    {
        $this->markTestSkipped('Signature::starmap is not implemented yet');
    }

    /**
     *
     */
    public function testGetName()
    {
        $this->assertEquals($this->fixtureArgs['name'], $this->fixture->getName());
    }

    /**
     *
     */
    public function testSetName()
    {
        $expected = 'test.new_name';
        $this->fixture->setName($expected);
        $this->assertEquals($expected, $this->fixture->getName());
    }

    /**
     * @expectedException \InvalidArgumentException
     * #expectedExceptionMessage argument must be of type [\Rhubarb\Rhubarb] [stdClass] given
     */
    public function testSetRhubarb()
    {
        $this->fixture->setRhubarb(new stdClass());
    }

    /**
     *
     */
    public function testGetRhubarb()
    {
        $this->assertInstanceOf('\Rhubarb\Rhubarb', $this->fixture->getRhubarb());
    }

    /**
     *
     */
    public function testIsMutable()
    {
        $this->assertFalse($this->fixture->isMutable(Signature::IMMUTABLE));
        $this->assertTrue($this->fixture->isMutable(Signature::MUTABLE));
    }

    /**
     *
     */
    public function testCopy()
    {
        /* Freeze Fixture then clone */
        $this->assertFalse($this->fixture->isFroze());
        $this->fixture->freeze();
        $this->assertTrue($this->fixture->isFroze());

        /* clone and ensure it is not frozen */
        $newfixture = $this->fixture->copy();
        $this->assertFalse($newfixture->isFroze());
    }

    /**
     *
     */
    public function testInvoke()
    {
        $expected = '\Rhubarb\Task\AsyncResult';
        $messageMock = $this->getMessageMock($this->rhubarb, $this->fixture);
        $asyncMock = $this->getAsyncResultMock($this->rhubarb, $messageMock);

        $this->rhubarb->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue($asyncMock));

        $actual = $this->fixture;
        $this->assertInstanceOf($expected, $actual());
    }

    /**
     *
     */
    public function testGetId()
    {
        $this->assertRegExp(
            '#([a-zA-Z0-9]{8})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{4})-([a-zA-Z0-9]{12})#',
            $this->fixture->getId()
        );
    }
}
 
