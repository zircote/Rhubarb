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
use Rhubarb\Connector\Predis as PredisConnector;
use Rhubarb\Task;

/**
 * @package     Rhubarb
 * @category    ResultStore
 */
class Predis extends PredisConnector implements ResultStoreInterface
{
    /**
     * @param \Rhubarb\Task $task
     * @return bool|mixed|string
     */
    public function getTaskResult(Task $task)
    {
        $pubsub = $this->getConnection()->pubSub();
        $pubsub->subscribe('celery-task-meta-' . $task->getId());
        foreach ($pubsub as $message) {
            if ($message->kind == 'message'){
                $message = json_decode($message->payload);
                $pubsub->unsubscribe('celery-task-meta-' . $task->getId());
                return $message;
            }
        }
    }
}
