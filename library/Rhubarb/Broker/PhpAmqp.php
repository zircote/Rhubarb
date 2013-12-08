<?php
namespace Rhubarb\Broker;

/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2012] [Robert Allen]
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
 * @category    Broker
 */
use Rhubarb\Connector\PhpAmqp as PhpAmqpConnector;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
use Rhubarb\Exception\Exception;
use Rhubarb\Message;
use Rhubarb\Rhubarb;

/**
 * @package     Rhubarb
 * @category    Broker
 *
 * Use:
 *
 * $options = array(
 *  'broker' => array(
 *      'type' => 'PhpAmqp',
 *      'options' => array(
 *          'uri' => 'amqp://celery:celery@localhost:5672/celery'
 *      )
 *  ),
 *  'result_store' => array(
 *      ...
 * );
 * $rhubarb = new \Rhubarb\Rhubarb($options);
 */
class PhpAmqp extends PhpAmqpConnector implements BrokerInterface
{
    
    /**
     * @param \Rhubarb\Task $task
     * @throws \Rhubarb\Exception\Exception
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        $connection = $this->getConnection();
        $connection->connect();
        $channel = new AMQPChannel($connection);
        
        if (!$channel->isConnected()) {
            throw new Exception('AMQP Failed to Connect');
        }
        $queue = new AMQPQueue($channel);
        
        $queue->setName($task->message->getQueue());
        $queue->setFlags(AMQP_DURABLE);
        if ($this->options['options']) {
            $queue->setArguments($this->options['options']);
        }
        $queue->declareQueue();
        
        $exchange = new AMQPExchange($channel);
        $exchange->setFlags(AMQP_PASSIVE|AMQP_DURABLE);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setName($task->message->getPropExchange());
        $exchange->declareExchange();
        $queue->bind($task->message->getPropExchange(), $task->getId());
        
        $msgProperties = array(
            'content_type' => $task->getMessage()->getContentType(),
            'content_encoding' => $task->getMessage()->getContentEncoding(),
            'encoding' => $task->getMessage()->getContentEncoding()
        );
        
        if ($task->getPriority()) {
            $msgProperties['priority'] = $task->getPriority();
        }
        $exchange->publish(
            (string) $task, 
            $task->getId(), 
            AMQP_NOPARAM,
            $msgProperties
        );
        $this->getConnection()->disconnect();
    }
}
