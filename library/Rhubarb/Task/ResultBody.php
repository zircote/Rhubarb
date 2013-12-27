<?php
namespace Rhubarb\Task;

/**
 *
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
 * @category    Rhubarb\Task
 */
use Rhubarb\Exception\RuntimeException;

/**
 * @package     Rhubarb
 * @category    Rhubarb\Task
 */
class ResultBody
{

    /**
     * @var string
     */
    protected $state = AsyncResult::PENDING;
    /**
     * @var
     */
    protected $traceback;
    /**
     * @var mixed
     */
    protected $result;
    /**
     * @var array
     */
    protected $children = array();

    public function __construct(array $body = array())
    {
        if (isset($body['state'])) {
            $this->setState($body['state']);
        }
        if (isset($body['traceback'])) {
            $this->setTraceback($body['traceback']);
        }
        if (isset($body['result'])) {
            $this->setResult($body['result']);
        }
        if (isset($body['children'])) {
            $this->setChildren($body['children']);
        }
    }

    /**
     *
     * @param array $children
     * @return ResultBody
     */
    protected function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param $result
     * @return $this
     * @throws \Rhubarb\Exception\RuntimeException
     */
    protected function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     *
     * @param string $state
     * @return ResultBody
     * @throws RuntimeException
     */
    protected function setState($state)
    {
        if (!in_array($state, AsyncResult::$allowedResultStates)) {
            throw new RuntimeException('status provided is not a known state');
        }
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     *
     * @param mixed $traceback
     * @return ResultBody
     */
    protected function setTraceback($traceback)
    {
        $this->traceback = $traceback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTraceback()
    {
        return $this->traceback;
    }
}
 
