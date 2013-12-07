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
use Rhubarb\Connector\PhpAmqp as PhpAmqpConnector;
use Rhubarb\Exception\Exception;
use Rhubarb\Rhubarb;
use Rhubarb\Task;
use Rhubarb\Exception\InvalidJsonException;
use AMQPQueue;
use AMQPChannel;

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
class PhpAmqp extends PhpAmqpConnector implements ResultStoreInterface
{

    public function getTaskResult(Task $task)
    {
        $result = null;
        try {
            $taskId = str_replace('-','', $task->getId());
            $connection = $this->getConnection();
            $connection->connect();
            if (!$connection->isConnected()) {
                throw new Exception;
            }
            $channel  = new AMQPChannel($connection);
            $queue = new AMQPQueue($channel);
            $queue->setName($taskId);
            $queue->setFlags(AMQP_DURABLE|AMQP_AUTODELETE);
            $queue->setArgument('x-expires', 86400000);
            $queue->declareQueue();
            if ($message = $queue->get()) {
                $messageBody = json_decode($message->getBody());
                if (json_last_error()) {
                    throw new InvalidJsonException('Serialization Error, result is not valid JSON');
                }
                $queue->ack($message->getDeliveryTag());
                $result = $messageBody;
                $queue->delete(AMQP_IFUNUSED|AMQP_IFEMPTY|AMQP_NOWAIT);
                $connection->disconnect();
            }
        } catch (\AMQPChannelException $e){
        }
        return $result;
    }
}
