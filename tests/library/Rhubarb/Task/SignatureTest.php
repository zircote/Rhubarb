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
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testSetBodyFrozen()
    {
        $this->fixture->freeze();
        $this->fixture->setBody(new Task\Body\Python());

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

    /**
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testSetHeaderFrozen()
    {
        $this->fixture->freeze();
        $this->fixture->setHeader('c_type', 'my_type');
    }
    
    public function testGetHeader()
    {
        $this->assertEquals($this->fixtureArgs['headers']['c_meth'], $this->fixture->getHeader('c_meth'));
    }

    public function testGetHeaderNotExisting()
    {
        $this->assertEquals(null, $this->fixture->getHeader('nada'));
    }

    /**
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testSetHeadersFrozen()
    {
        $this->fixture->freeze();
        $expected = array('lang' => 'py', 'c_type' => 'my_type');
        $this->fixture->setHeaders($expected);
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
    public function testGetHeadersNoBody()
    {
        
        $this->fixture = new Signature(
            $this->rhubarb,
            $this->fixtureArgs['name']);
        $this->assertEquals(array('c_type' => __CLASS__), $this->fixture->getHeaders());
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
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testSetPropertyWhileFroze()
    {
        $additive = array('content_type' => 'application/json');
        $expected = array_merge($this->fixtureArgs['properties'], $additive);
        $this->fixture->freeze();
        $this->fixture->setProperty(key($additive), current($additive));
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
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testSetPropertiesWhileFrozen()
    {
        $additive = array('content_type' => 'application/json');
        $expected = array_merge($this->fixtureArgs['properties'], $additive);
        $this->fixture->freeze();
        $this->fixture->setProperties($expected);
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
    public function testSWithBody()
    {
        $expected = '\Rhubarb\Task\Signature';
        $actual = $this->fixture->s(new Task\Body\Python(array()));
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
    public function testSiWithBody()
    {
        $expected = '\Rhubarb\Task\Signature';
        $actual = $this->fixture->si(new Task\Body\Python());
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

        $actual = $this->fixture->applyAsync(null, array('prop' => 1), array('head' => 2));
        $this->assertArrayHasKey('prop', $this->fixture->getProperties());
        $this->assertArrayHasKey('head', $this->fixture->getHeaders());
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testApplyAsyncWhileFrozen()
    {
        $this->fixture->freeze();
        $this->fixture->applyAsync(null, array('prop' => 1), array('head' => 2));
        
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
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testSetNameFrozen()
    {
        $expected = 'test.new_name';
        $this->fixture->freeze();
        $this->fixture->setName($expected);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage argument must be of type [\Rhubarb\Rhubarb] [stdClass] given
     */
    public function testSetRhubarbWithStdClass()
    {
        $this->fixture->setRhubarb(new stdClass());
    }

    /**
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testSetRhubarbWhenFrozen()
    {
        $this->fixture->freeze();
        $this->fixture->setRhubarb(new stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage argument must be of type [\Rhubarb\Rhubarb] [integer] given
     */
    public function testSetRhubarbNotObject()
    {
        $this->fixture->setRhubarb(1);
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
    public function testOnSuccess()
    {
        $this->fixture->onSuccess(function($task){ $task->getId();});
    }

    /**
     *
     */
    public function testOnFailure()
    {
        $this->fixture->onFailure(function($task){ $task->getId();});
    }

    /**
     *
     */
    public function testOnRetry()
    {
        $this->fixture->onRetry(function($task){ $task->getId();});
    }

    /**
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testOnSuccessWhileFrozen()
    {
        $this->fixture->freeze();
        $this->fixture->onSuccess(function($task){ $task->getId();});
    }

    /**
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testOnFailureWhileFrozen()
    {
        $this->fixture->freeze();
        $this->fixture->onFailure(function($task){ $task->getId();});
    }

    /**
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Signature is Frozen
     */
    public function testOnRetryWhileFrozen()
    {
        $this->fixture->freeze();
        $this->fixture->onRetry(function($task){ $task->getId();});
    }

}
 
