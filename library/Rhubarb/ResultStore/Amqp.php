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
use Rhubarb\Exception\InvalidJsonException;
use AMQP\Exception\ChannelException;
use AMQP\Connection;
use Rhubarb\Rhubarb;
use Rhubarb\Task;
use Rhubarb\Exception\CeleryConfigurationException;

/**
 * @package     Rhubarb
 * @category    ResultStore
 *
 * Use:
 *
 * $options = array(
 *  'broker' => array(
 *  ...
 *  ),
 *  'result_store' => array(
 *      'type' => 'Amqp',
 *      'options' => array(
 *          'uri' => 'amqps://celery:celery@localhost:5671/celery_results',
 *          'options' => array(
 *              'ssl_options' => array(
 *                  'verify_peer' => true,
 *                  'allow_self_signed' => true,
 *                  'cafile' => 'some_ca_file'
 *                  'capath' => '/etc/ssl/ca,
 *                  'local_cert' => /etc/ssl/self/key.pem'
 *              ),
 *          )
 *      )
 *  )
 * );
 * $rhubarb = new \Rhubarb\Rhubarb($options);
 */
class Amqp extends \Rhubarb\Connector\Amqp implements ResultStoreInterface
{

    /**
     * @param Task $task
     * @return bool|mixed|null|string
     * @throws \Rhubarb\Exception\InvalidJsonException
     * @throws CeleryConfigurationException
     */
    public function getTaskResult(Task $task)
    {
        $result = null;

        try {
            $taskId = str_replace('-', '', $task->getId());
            $channel = $this->getConnection()->channel();

            if ($message = $channel->basicGet(array('queue' => $taskId))) {
                $content_type = $message->get('content_type');

                if ($content_type !== 'application/json') {
                    throw new CeleryConfigurationException("Response's content-type is not application/json. Got: {$content_type}. Make sure, that CELERY_RESULT_SERIALIZER is set to \"json\"");
                }

                $messageBody = json_decode($message->body);

                if (json_last_error()) {
                    throw new InvalidJsonException('Serialization Error, result is not valid JSON');
                }

                $channel->basicAck($message->delivery_info['delivery_tag']);
                $channel->queueDelete(
                    array('queue' => $taskId, 'if_unused' => true, 'if_empty' => true, 'no_wait' => true)
                );
                $channel->close();

                $result = $messageBody;
            }
        } catch (\AMQP\Exception\ChannelException $e) {
        }

        return $result;
    }
}
