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
use Rhubarb\Connector\Predis as PredisConnection;
use Rhubarb\Message;
use Rhubarb\Rhubarb;
use Rhumsaa\Uuid\Uuid;

/**
 * @package     Rhubarb
 * @category    Broker
 */
class Predis extends PredisConnection implements BrokerInterface
{
    /**
     * @param \Rhubarb\Task $task
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        $task->getMessage()->setContentEncoding(Rhubarb::CONTENT_ENCODING_UTF8);
        if (!$task->getMessage()->getPropRoutingKey()) {
            $task->getMessage()->setPropRoutingKey('celery');
        }
        if (!$task->getMessage()->getCorrelationId()){
            $task->getMessage()->setCorrelationId($task->getId());
        }
        if (!$task->getMessage()->getReplyTo()){
            $task->getMessage()->setReplyTo($task->getId());
        }
        $task->getMessage()->setPropDeliveryMode(2)->setPropDeliveryTag(2);
        $task->getMessage()->setBodyEncoding(Rhubarb::CONTENT_ENCODING_BASE64);
        $task->toArray();
        $this->getConnection()->lpush($task->getMessage()->getPropExchange(), (string) $task->getMessage());
    }
}
