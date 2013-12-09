<?php
namespace Rhubarb\Broker;

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
 * @category    Broker
 */
use Rhubarb\Connector\Predis as PredisConnection;
use Rhubarb\Exception\ConnectionException;
use Rhubarb\Message\Message;
use Rhubarb\Rhubarb;

/**
 * @package     Rhubarb
 * @category    Broker
 */
class Predis extends PredisConnection implements BrokerInterface
{
    /**
     * @param Message $message
     * @return \Rhubarb\Task\AsyncResult
     * @throws \Rhubarb\Exception\ConnectionException
     */
    public function publishTask(Message $message)
    {
        if (!$this->getConnection()) {
            throw new ConnectionException(sprintf('Connection is not defined in [%s]', __METHOD__));
        }
        return $this->getConnection()
            ->lpush($message->getHeader('exchange') ?: Rhubarb::DEFAULT_EXCHANGE_NAME, $message->serialize());
    }

}
