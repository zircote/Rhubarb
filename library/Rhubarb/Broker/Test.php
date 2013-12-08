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
/**
 * @package     Rhubarb
 * @category    Broker
 */
class Test implements BrokerInterface
{
    protected $exception;
    protected $published;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {

    }

    public function setOptions(array $options)
    {
        
    }
    
    public function getOptions()
    {
        
    }
    /**
     * @param \Exception $exception
     */
    public function throwExceptionOnNextRequest(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     *
     */
    public function reset()
    {
        $this->exception = null;
        $this->published = null;
    }

    /**
     * @param \Rhubarb\Task $task
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        
        $taskArray = $task->toArray();
        $this->published = json_encode($taskArray);
    }

    public function getPublishedValues()
    {
        return $this->published;
    }
}
