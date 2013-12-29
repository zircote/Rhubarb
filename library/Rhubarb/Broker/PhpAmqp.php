<?php
namespace Rhubarb\Broker;

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
 * @category    Broker
 */
use Rhubarb\Connector\PhpAmqp as PhpAmqpConnector;
use Rhubarb\Rhubarb;
use Rhubarb\Task\Message;
use Rhubarb\Task\AsyncResult;

/**
 * @package     Rhubarb
 * @category    Broker
 */
class PhpAmqp extends PhpAmqpConnector implements BrokerInterface
{
    /**
     * @var \AMQPChannel
     */
    protected $channel;

    /**
     * @param Message $message
     * @throws \Rhubarb\Exception\Exception
     * @return AsyncResult
     */
    public function publishTask(Message $message)
    {
        if (!$this->getConnection()->isConnected()) {
            $this->getConnection()->connect();
        }
        $this->declareQueue($message);
        $this->getExchange($this->getChannel(), $message)
            ->publish(
                $message->serialize(),
                $message->getId(),
                AMQP_NOPARAM,
                $this->formatProperties($message)
            );
        
        if (isset($events['enabled']) && $events['enabled']) {
            $payload = $message->getPayload();
            $payload = $this->getRhubarb()->serialize($payload['sent_event']);
            $this->declareEventsQueue();
            $this->getEventsExchange($this->getChannel())
                ->publish(
                    $payload,
                    Rhubarb::EVENTS_EXCHANGE_NAME,
                    AMQP_NOPARAM
                );
        }
        $this->getConnection()->disconnect();
    }

    /**
     * @param Message $message
     * @return array
     */
    protected function formatProperties(Message $message)
    {
        $msgProperties = $message->getProperties();
        $msgProperties['headers'] = $message->getHeaders();
        return $msgProperties;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param Message $message
     * @return \AMQPQueue
     */
    protected function declareQueue(Message $message)
    {
        if (!$this->getConnection()->isConnected()) {
            $this->getConnection()->reconnect();
        }
        $amqpQueue = new \AMQPQueue($this->getChannel());
        $amqpQueue->setName($message->getProperty('queue'));
        $amqpQueue->setFlags(AMQP_DURABLE);

        $amqpQueue->declareQueue();
        $amqpQueue->bind($message->getProperty('exchange'), $message->getId());
        return $amqpQueue;
    }

    /**
     * @return \AMQPQueue
     */
    protected function declareEventsQueue()
    {
        $amqpQueue = new \AMQPQueue($this->getChannel());
        $amqpQueue->setName(Rhubarb::EVENTS_EXCHANGE_NAME);
        $amqpQueue->setFlags(AMQP_DURABLE);

        $amqpQueue->declareQueue();
        $amqpQueue->bind(Rhubarb::EVENTS_EXCHANGE_NAME, Rhubarb::EVENTS_EXCHANGE_NAME);
        return $amqpQueue;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return \AMQPChannel
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

    /**
     * @codeCoverageIgnore
     *
     * @param \AMQPChannel $amqpChannel
     * @return \AMQPExchange
     */
    protected function getEventsExchange($amqpChannel)
    {
        if (!$this->getConnection()->isConnected()) {
            $this->getConnection()->reconnect();
        }
        $amqpExchange = new \AMQPExchange($amqpChannel);

        $amqpExchange->setFlags(AMQP_PASSIVE | AMQP_DURABLE);
        $amqpExchange->setType(AMQP_EX_TYPE_DIRECT);
        $amqpExchange->setName(Rhubarb::EVENTS_EXCHANGE_NAME);

        $amqpExchange->declareExchange();
        return $amqpExchange;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param \AMQPChannel $amqpChannel
     * @param Message $message
     * @return \AMQPExchange
     */
    protected function getExchange($amqpChannel, $message)
    {
        if (!$this->getConnection()->isConnected()) {
            $this->getConnection()->reconnect();
        }
        $amqpExchange = new \AMQPExchange($amqpChannel);

        $amqpExchange->setFlags(AMQP_PASSIVE | AMQP_DURABLE);
        $amqpExchange->setType(AMQP_EX_TYPE_DIRECT);
        $amqpExchange->setName($message->getProperty('exchange'));

        $amqpExchange->declareExchange();
        return $amqpExchange;
    }

}
