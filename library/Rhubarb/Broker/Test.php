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
 * 
 */
use Rhubarb\Message\Message;
use Rhubarb\Task\AsyncResult;

/**
 * @package     Rhubarb
 * @category    Broker
 * @codeCoverageIgnore
 */
class Test implements BrokerInterface
{
    protected $exception;
    protected $published;

    /**
     *
     */
    public function reset()
    {
        $this->exception = null;
        $this->published = null;
    }

    /**
     * @param \Rhubarb\Message\Message $message
     * @return AsyncResult
     */
    public function publishTask(Message $message)
    {
    }

    public function getPublishedValues()
    {
        return $this->published;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        // TODO: Implement getHeaders() method.
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        // TODO: Implement getProperties() method.
    }

}
