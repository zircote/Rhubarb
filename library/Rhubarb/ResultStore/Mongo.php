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
use Rhubarb\Connector\Mongo as MongoConnection;

/**
 * @package     Rhubarb
 * @category    ResultStore
 */
class Mongo extends MongoConnection implements ResultStoreInterface
{
    
    public function getTaskResult(\Rhubarb\Task $task)
    {
        $collection = $this->getConnection()->selectCollection(self::CELERY_TASK_META);
        echo $task->getId(), PHP_EOL;
        $result = $collection->findOne(array('_id' => (string) $task->getId()));
        if ($result) {
            foreach ($result as $k => $v) {
                if ($v instanceof \MongoBinData) {
                    $v = $v->bin;
                } elseif ($v instanceof \MongoDate) {
                    $v = $v->sec;
                }
                $result[$k] = $v;
            }
            $result = (object) $result;
        }
        return $result;
    }
}
