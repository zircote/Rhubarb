<?php
namespace Rhubarb\ResultStore;

/**
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
 * @category    ResultStore
 */
use Rhubarb\Connector\PhpAmqp as PhpAmqpConnector;
use Rhubarb\Task\AsyncResult;
use Rhubarb\Task\ResultBody;
use Rhubarb\Exception\InvalidJsonException;

/**
 * @package     Rhubarb
 * @category    ResultStore
 *
 */
class PhpAmqp extends PhpAmqpConnector implements ResultStoreInterface
{

    protected $channel;
    protected $queue;

    /**
     * @param AsyncResult $task
     * @return ResultBody
     * @throws InvalidJsonException
     */
    public function getTaskResult(AsyncResult $task)
    {
        $result = $task->getResult();
        try {
            if (!$this->getConnection()->isConnected()) {
                $this->getConnection()->connect();
            }
            $queue = $this->declareQueue($task);
            if ($message = $queue->get()) {
                $queue->ack($message->getDeliveryTag());
                $queue->delete(AMQP_IFUNUSED | AMQP_IFEMPTY | AMQP_NOWAIT);
                $result = new ResultBody(
                    $this->getRhubarb()->unserialize($message->getBody(), $message->getHeader('content_type'))
                );
            }
        } catch (\AMQPChannelException $e) {
            $this->getRhubarb()->getLogger()->warn($e);
        }
        return $result;
    }

    /**
     * @param AsyncResult $result
     * @return \AMQPQueue
     * @codeCoverageIgnore
     */
    protected function declareQueue(AsyncResult $result)
    {
        if (!$this->getConnection()->isConnected()) {
            $this->getConnection()->reconnect();
        }
        $taskId = str_replace('-', '', $result->getId());
        $queue = new \AMQPQueue($this->getChannel());
        $queue->setName($taskId);
        $queue->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
        /* @todo Fix this to be configurable */
        $queue->setArgument('x-expires', 86400000);
        $queue->declareQueue();
        return $queue;
    }

    /**
     * @return \AMQPChannel
     * @codeCoverageIgnore
     */
    protected function getChannel()
    {
        if (!$this->channel) {
            if (!$this->getConnection()->isConnected()) {
                $this->getConnection()->reconnect();
            }
            $this->channel = new \AMQPChannel($this->getConnection());
        }

        return $this->channel;
    }
}
