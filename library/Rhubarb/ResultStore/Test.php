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
use Rhubarb\Task\ResultBody;
use Rhubarb\Task\AsyncResult;
use Rhubarb\Connector\AbstractTestConnector;

/**
 * @package     Rhubarb
 * @category    ResultStore
 * @codeCoverageIgnore
 */
class Test extends AbstractTestConnector
{


    protected $exception;
    protected $wait = 0;
    protected $timer;


    public function setWait($wait = 0)
    {
        $this->wait = $wait;
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
        $this->timer = null;
        $this->wait = 0;
    }

    /**
     * @param AsyncResult $task
     * @return ResultBody
     */
    public function getTaskResult(AsyncResult $task)
    {
        if (!$this->timer) {
            $this->timer = time() + $this->wait;
        }
        if ($this->timer <= time()) {
            return static::$nextResult;
        }
        return new ResultBody();
    }
}
