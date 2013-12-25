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
use Rhubarb\Rhubarb;
use Rhubarb\Task\AsyncResult;
use Rhubarb\Connector\AbstractTestConnector;
use Rhubarb\Task\ResultBody;

/**
 * @package     Rhubarb
 * @category    Broker
 * @codeCoverageIgnore
 */
class Test extends AbstractTestConnector
{
    static protected $deliveryTag = 0;
    protected $rhubarb;
    protected $exception;
    protected $published;
    protected $callback;
    


    /**
     * @param \Rhubarb\Rhubarb $rhubarb
     * @param array $options
     */
    public function __construct(Rhubarb $rhubarb, array $options = array())
    {
        $this->setOptions($options);
        $this->setRhubarb($rhubarb);
        $this->callback = function($jsonString)
        {
            return $jsonString;
        };
    }
    
    /**
     *
     */
    public function reset()
    {
        $this->exception = null;
        $this->published = null;
        static::$deliveryTag = 0;
    }

    /**
     * @param \Rhubarb\Message\Message $message
     * @return AsyncResult
     */
    public function publishTask(Message $message)
    {
        $message->setProperty('delivery_tag', ++static::$deliveryTag);
        static::$nextResult = call_user_func($this->callback, array($message->serialize()));
        return true;
    }
    public function setTaskCallback(callable $callback)
    {
        $this->callback = $callback;
    }
}
