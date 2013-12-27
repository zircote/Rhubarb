<?php
namespace Rhubarb;

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
     * @package     Rhubarb
     * @category    Rhubarb\Connector
     */

/**
 * @package     Rhubarb
 * @category    Rhubarb\Connector
 */
class PhpAmqpTestCase extends RhubarbTestCase
{

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AMQPChannel
     */
    public function getAMQPChannelMock()
    {
        $methods = array('commitTransaction', 'isConnected', 'qos',
            'rollbackTransaction', 'setPrefetchCount', 'setPrefetchSize', 'startTransaction'
        );
        $AMQPChannel = $this->getMock('AMQPChannel', $methods, array(), '', false);
        return $AMQPChannel;
    }

    /**
     * @param bool $callOriginalConstructor
     * @param array $methods
     * @param array $constructorArguments
     * @return \PHPUnit_Framework_MockObject_MockObject|\AMQPConnection
     */
    public function getAMQPConnectionMock($callOriginalConstructor = true, $methods = array(
        'connect', 'disconnect', 'getHost', 'getLogin', 'getPassword', 'getPort', 'getVhost',
        'isConnected', 'reconnect', 'setHost', 'setLogin', 'setPassword', 'setPort', 'setVhost',
        'setTimeout', 'getTimeout', 'getReadTimeout', 'setReadTimeout', 'getWriteTimeout', 'setWriteTimeout',
        'pconnect', 'pdisconnect'
    ), $constructorArguments = array())
    {
        $AMQPConnection = $this->getMock(
            'AMQPConnection', $methods, $constructorArguments, '', $callOriginalConstructor
        );
        return $AMQPConnection;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param string $method
     * @param \PHPUnit_Framework_MockObject_Stub $returns
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder|null $expects
     * @return \PHPUnit_Framework_MockObject_MockObject|\Predis\Client
     */
    public function setAMQPMethod($mock, $method, $returns, $expects = null)
    {
        $mock->expects($expects ? : $this->any())
            ->method($method)
            ->will($returns);
        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AMQPEnvelope
     */
    public function getAMQPEnvelopeMock()
    {
        $methods = array(
            'getAppId', 'getBody', 'getContentEncoding', 'getContentType', 'getCorrelationId', 'getDeliveryTag',
            'getExchangeName', 'getHeader', 'getHeaders', 'getMessageId', 'getPriority', 'getReplyTo', 'getTimeStamp',
            'getType', 'getUserId', 'isRedelivery'
        );
        $AMQPEnvelope = $this->getMock('AMQPEnvelope', $methods, array(), '', false);
        return $AMQPEnvelope;
    }

    /**
     * @param bool $callOriginalConstructor
     * @param array $methods
     * @param array $constructorArguments
     * @return \PHPUnit_Framework_MockObject_MockObject|\AMQPExchange
     */
    public function getAMQPExchangeMock($callOriginalConstructor = true, $methods = array(
        'bind', 'declareExchange', 'delete', 'getArgument', 'getArguments', 'getFlags', 'getName',
        'getType', 'publish', 'setArgument', 'setArguments', 'setFlags', 'setName', 'setType'
    ), $constructorArguments = array())
    {
        $AMQPExchange = $this->getMock('AMQPExchange', $methods, $constructorArguments, '', $callOriginalConstructor);
        return $AMQPExchange;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\AMQPQueue
     */
    public function getAMQPQueueMock()
    {
        $methods = array(
            'ack', 'bind', 'cancel', 'consume', 'declareQueue', 'delete', 'get', 'getArgument',
            'getFlags', 'getName', 'nack', 'purge', 'setArgument', 'setArguments', 'setFlags',
            'setName', 'unbind'
        );
        $AMQPQueue = $this->getMock('AMQPQueue', $methods, array(), '', false);
        return $AMQPQueue;
    }
}
 
