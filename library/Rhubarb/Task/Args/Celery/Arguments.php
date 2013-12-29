<?php
namespace Rhubarb\Task\Args\Celery;

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
     * @category    Rhubarb\Celery
     */

/**
 * @package     Rhubarb
 * @category    Rhubarb\Celery
 */
class Arguments
{
    /**
     * @var string
     */
    protected $signal;
    /**
     * @var bool
     */
    protected $terminate = false;
    /**
     * @var string
     */
    protected $task_id;

    /**
     * @param string $task_id
     * @param bool $terminate
     * @param null|string $signal
     */
    public function __construct($task_id, $terminate = false, $signal = null)
    {
        $this->signal = $signal;
        $this->terminate = $terminate;
        $this->task_id = $task_id;
    }

    /**
     * @param string $task_id
     * @param bool $terminate
     * @param null|string $signal
     * @return Arguments
     */
    static public function factory($task_id, $terminate = false, $signal = null)
    {
        return new self($task_id, $terminate, $signal);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'signal' => $this->signal,
            'terminate' => $this->terminate,
            'task_id' => $this->task_id
        );
    }
}
 
