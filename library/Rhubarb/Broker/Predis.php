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
use Rhubarb\Connector\Predis as PredisConnection;
use Rhubarb\Exception\ConnectionException;
use Rhubarb\Task\AsyncResult;
use Rhubarb\Task\Message;
use Rhubarb\Rhubarb;

/**
 * @package     Rhubarb
 * @category    Broker
 */
class Predis extends PredisConnection implements BrokerInterface
{
    static protected $deliveryTag = 0;

    /**
     * @param Message $message
     * @return \Rhubarb\Task\AsyncResult
     * @throws \Rhubarb\Exception\ConnectionException
     */
    public function publishTask(Message $message)
    {
        $message->setProperty('delivery_tag', ++static::$deliveryTag);
        $result = $this->getConnection()
            ->lpush($message->getHeader('exchange') ? : Rhubarb::DEFAULT_EXCHANGE_NAME, $message->serialize());
        $events = $this->getRhubarb()->getEventOptions();
        if (isset($events['enabled']) && $events['enabled']) {
            $payload = $message->getPayload();
            $payload = $this->getRhubarb()->serialize($payload['sent_event']);
            $this->getConnection()->set(Rhubarb::EVENTS_EXCHANGE_NAME, $payload);
        }
        return $result;
    }

}
