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
use Rhubarb\Exception\Exception;
use Rhubarb\Rhubarb;
use Rhubarb\Task;
use Rhubarb\Exception\InvalidJsonException;
use AMQPConnection;
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
class PhpAmqp extends AbstractResultStore
{
    /**
     * @var AmqpConnection
     */
    protected $connection;
    /**
     * @var array
     */
    protected $options = array(
        'connection' => array(
            'host' => '127.0.0.1',
            'port' => 5672,
            'vhost' => '/',
            'login' => 'guest',
            'password' => 'guest'
        ),
        'options' => array()
    );

    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

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
            $queue->setArgument('x-expires', 86400000);
            $queue->declare();
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

    /**
     * @return AMQPConnection
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $options = $this->getOptions();
            $connection = new AMQPConnection($options['connection']);
            $this->setConnection($connection);
        }
        return $this->connection;
    }

    /**
     * @param AMQPConnection $connection
     *
     * @return AMQP
     */
    public function setConnection(AMQPConnection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @param array $options
     *
     * @return AMQP
     *
     * @throws \UnexpectedValueException
     */
    public function setOptions(array $options)
    {
        if (isset($options['exchange'])) {
            if (!is_string($options['exchange'])) {
                throw new \UnexpectedValueException('exchange value is not a string, a string is required');
            }
            $this->exchange = $options['exchange'];
            unset($options['exchange']);
        }
        if (isset($options['queue'])) {
            if (isset($options['queue']['arguments'])) {
                $this->queueOptions = $options['queue'];
            }
            unset($options['queue']);
        }
        if (isset($options['uri'])) {
            $uri = parse_url($options['uri']);
            if (!isset($uri['port'])) {
                $uri['scheme'] == 'amqps' ? 5673 : $this->options['connection']['port'];
            } else {
                $port = isset($uri['port']) ? $uri['port'] : $this->options['connection']['port'];
            }
            unset($options['uri']);
            $options['connection']['host'] = $uri['host'];
            $options['connection']['port'] = $port;
            $options['connection']['vhost'] = isset($uri['path']) ? $uri['path'] : $this->options['connection']['path'];
            $options['connection']['login'] = isset($uri['username']) ? $uri['username'] : $this->options['connection']['login'];
            $options['connection']['password'] = isset($uri['pass']) ? $uri['pass'] : $this->options['connection']['password'];
            $this->options['connection'] = $options['connection'];
        }
        return $this;
    }
}
