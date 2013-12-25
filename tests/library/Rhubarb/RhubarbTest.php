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
use Rhubarb\ResultStore\ResultStoreInterface;
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
    protected $options = array(
            'broker' => array(
                'type' => 'Test',
                'class_namespace' => Rhubarb::BROKER_NAMESPACE
            ),
            'result_store' => array(
                'type' => 'Test',
                'class_namespace' => Rhubarb::RESULTSTORE_NAMESPACE
            ),
            'tasks' => array(
                array(
                    'name' => 'app.add', // c_type
                    'headers' => array(
                        'timelimit' => array(5, 10)
                    )
                ),
            ),
            'logger' => array(
                'loggers' => array(
                    'dev' => array(
                        'level' => 'DEBUG',
                        'appenders' => array(__NAMESPACE__),
                    ),
                ),
                'appenders' => array(
                    __NAMESPACE__ => array(
                        'class' => 'LoggerAppenderNull'
                    )
                )
            )
        );

    /**
     *
     */
    public function setup()
    {
        $this->rhubarb = new Rhubarb($this->options);

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
        $this->assertEquals(array(5, 10), $asyncResult->getHeader('timelimit'));
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
    public function testGetBroker()
    {
        $this->assertInstanceOf('\Rhubarb\Broker\Test', $this->rhubarb->getBroker());
    }
    public function testGetResultStore()
    {
        $this->assertInstanceOf('\Rhubarb\ResultStore\Test', $this->rhubarb->getResultStore());
    }

    public function testGetOptions()
    {
        $this->assertEquals($this->options, $this->rhubarb->getOptions());
    }

    public function testGetOption()
    {
        $this->assertEquals($this->options['tasks'], $this->rhubarb->getOption('tasks'));
        $this->assertNull($this->rhubarb->getOption('non-existent'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage invalid options name
     */
    public function testSetOptionWithInvalidName()
    {
        $this->rhubarb->setOption(array('name'), array());
    }

    public function testSetOption()
    {
        $this->assertEquals($this->options['tasks'], $this->rhubarb->getOption('tasks'));
        $this->rhubarb->setOption('tasks', array());
        $this->assertEquals(array(), $this->rhubarb->getOption('tasks'));
    }

    public function testSetTasks()
    {
        $this->rhubarb->setOption('tasks', array());
        $this->assertEquals(array(), $this->rhubarb->getOption('tasks'));
        $this->rhubarb->setTasks($this->options['tasks']);
        $this->assertEquals($this->options['tasks'], $this->rhubarb->getOption('tasks'));
    }

    public function testAddTask()
    {
        $this->rhubarb->addTask(array('name' => 'test.task.mine', 'properties' => array('body_encoding' => 'utf-8')));
        $actual = $this->rhubarb->task('test.task.mine');
        $this->assertInstanceOf('\Rhubarb\Task\Signature', $actual);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage task name must be declared in the task definition
     */
    public function testAddTaskWithNoName()
    {
        $this->rhubarb->addTask(array('properties' => array('body_encoding' => 'utf-8')));
    }

    /**
     * @expectedException \Rhubarb\Exception\TaskSignatureException
     * @expectedExceptionMessage Task [test.task.mine] is not in registered
     */
    public function testDelTask()
    {
        $this->rhubarb->addTask(array('name' => 'test.task.mine'));
        $actual = $this->rhubarb->task('test.task.mine');
        $this->assertInstanceOf('\Rhubarb\Task\Signature', $actual);
        $this->rhubarb->delTask('test.task.mine');
        $this->rhubarb->task('test.task.mine');
    }

    public function testDecodeBase64()
    {
        $encodedBody = 'eyJkZXN0aW5hdGlvbiI6IG51bGwsICJtZXRob2QiOiAicmV2b2tlIiwgImFyZ3VtZW50cyI6IHsic2lnbmFsIjogbnVsbCwgInRlcm1pbmF0ZSI6IGZhbHNlLCAidGFza19pZCI6ICJjNzJlOTlkZC0xM2FhLTQ3OTEtOGIyMS02YWU5NDU5NDk3MmIifX0=';
        $actual = $this->rhubarb->decode($encodedBody, Rhubarb::CONTENT_ENCODING_BASE64);
        $expected = '{"destination": null, "method": "revoke", "arguments": {"signal": null, "terminate": false, "task_id": "c72e99dd-13aa-4791-8b21-6ae94594972b"}}';
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testDecodeUTF8()
    {
        $expected = '{"destination": null, "method": "revoke", "arguments": {"signal": null, "terminate": false, "task_id": "c72e99dd-13aa-4791-8b21-6ae94594972b"}}';
        $actual = $this->rhubarb->decode($expected, Rhubarb::CONTENT_ENCODING_UTF8);
        $this->assertJsonStringEqualsJsonString($expected, $actual);
    }

    public function testEncodeBase64()
    {
        $body = '{"destination": null, "method": "revoke", "arguments": {"signal": null, "terminate": false, "task_id": "c72e99dd-13aa-4791-8b21-6ae94594972b"}}';
        $expected = 'eyJkZXN0aW5hdGlvbiI6IG51bGwsICJtZXRob2QiOiAicmV2b2tlIiwgImFyZ3VtZW50cyI6IHsic2lnbmFsIjogbnVsbCwgInRlcm1pbmF0ZSI6IGZhbHNlLCAidGFza19pZCI6ICJjNzJlOTlkZC0xM2FhLTQ3OTEtOGIyMS02YWU5NDU5NDk3MmIifX0=';
        $actual = $this->rhubarb->encode($body, Rhubarb::CONTENT_ENCODING_BASE64);
        $this->assertEquals($expected, $actual);
    }

    public function testEncodeUTF8()
    {
        $expected = '{"destination": null, "method": "revoke", "arguments": {"signal": null, "terminate": false, "task_id": "c72e99dd-13aa-4791-8b21-6ae94594972b"}}';
        $actual = $this->rhubarb->encode($expected, Rhubarb::CONTENT_ENCODING_UTF8);
        $this->assertEquals($expected, $actual);
    }

    public function testUnserializeJSON()
    {
        $json = '{"test": "win"}';
        $expected = array('test' => 'win');
        $actual = $this->rhubarb->unserialize($json, Rhubarb::CONTENT_TYPE_JSON);
        $this->assertEquals($expected, $actual);
    }

    public function testSerializeJSON()
    {
        $expected = '{"test":"win"}';
        $json = array('test' => 'win');
        $actual = $this->rhubarb->serialize($json, Rhubarb::CONTENT_TYPE_JSON);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Rhubarb\Exception\EncodingException
     * @expectedExceptionMessage failed to encode payload of type [unknown] ensure it is declared in your configuration
     */
    public function testUnknownEncodeException(){
        $this->rhubarb->encode('blah', 'UNKNOWN');
    }

    /**
     * @expectedException \Rhubarb\Exception\EncodingException
     * @expectedExceptionMessage failed to decode payload of type [unknown] ensure it is declared in your configuration
     */
    public function testUnknownDecodeException(){
        $this->rhubarb->decode('blah', 'UNKNOWN');
    }
    
    /**
     * @expectedException \Rhubarb\Exception\MessageUnserializeException
     * @expectedExceptionMessage failed to serialize payload of type [unknown] ensure it is declared in your configuration
     */
    public function testUnknownSerializeException(){
        
        $this->rhubarb->serialize('blah', 'UNKNOWN');
    }
    
    /**
     * @expectedException \Rhubarb\Exception\MessageUnserializeException
     * @expectedExceptionMessage failed to unserialize payload of type [unknown] ensure it is declared in your configuration
     */
    public function testUnknownUnSerializeException(){
        $this->rhubarb->unserialize('blah', 'UNKNOWN');
    }
    
    public function testSetResultStoreWithResultStore()
    {
        $this->rhubarb->setResultStore($this->getMock('\Rhubarb\ResultStore\ResultStoreInterface'));
        $this->assertInstanceOf('\Rhubarb\ResultStore\ResultStoreInterface', $this->rhubarb->getResultStore());
        $this->assertNotInstanceOf('\Rhubarb\ResultStore\Test', $this->rhubarb->getResultStore());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expecteExceptionMessage ResultStore class [IDoNotExistsClassName] unknown
     */
    public function testSetResultStoreWithNonExistentClassName()
    {
        $this->rhubarb->setResultStore(array('type' => 'IDoNotExistsClassName'));
    }
    public function testSeBrokerWithResultStore()
    {
        $this->rhubarb->setBroker($this->getMock('\Rhubarb\Broker\BrokerInterface'));
        $this->assertInstanceOf('\Rhubarb\Broker\BrokerInterface', $this->rhubarb->getBroker());
        $this->assertNotInstanceOf('\Rhubarb\Broker\Test', $this->rhubarb->getBroker());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expecteExceptionMessage Broker class [IDoNotExistsClassName] unknown
     */
    public function testSetBrokerWithNonExistentClassName()
    {
        $this->rhubarb->setBroker(array('type' => 'IDoNotExistsClassName'));
    }
    public function testDispatch()
    {
        $sig = $this->rhubarb->t('app.add');
        $asyncres = $sig->applyAsync();
        $this->assertInstanceOf('\Rhubarb\Task\AsyncResult', $asyncres);
    }
}
