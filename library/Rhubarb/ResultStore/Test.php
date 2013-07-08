<?php
namespace Rhubarb\ResultStore;

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
 * @category    ResultStore
 */
/**
 * @package     Rhubarb
 * @category    ResultStore
 */
class Test implements ResultStoreInterface
{

    protected $nextResult;
    protected $exception;

    protected $wait = 0;
    protected $timer;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {

    }

    public function setWait($wait = 0)
    {
        $this->wait = $wait;
    }
    /**
     * @param $result
     */
    public function setNextResult($result)
    {
        $this->nextResult = $result;
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
        $this->nextResult = null;
        $this->exception = null;
    }

    /**
     * @param \Rhubarb\Task $task
     *
     * @return bool|string
     */
    public function getTaskResult(\Rhubarb\Task $task)
    {
        if(!$this->timer){
            $this->timer = time() + $this->wait;
        }
        if($this->timer <= time()){
            return json_decode($this->nextResult);
        }
        return null;
    }
    public function setOptions(array $options)
    {

    }
}
