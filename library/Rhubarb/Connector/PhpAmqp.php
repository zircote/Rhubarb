<?php
namespace Rhubarb\Connector;

/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2013] [Robert Allen]
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
 * @package
 * @category
 * @subcategory
 */
use Rhubarb\Exception\ConnectionException;

/**
 * @package
 * @category
 * @subcategory
 */
class PhpAmqp extends AbstractConnector
{

    /**
     * @var \AmqpConnection
     */
    protected $connection;
    /**
     * @var array
     */
    protected $options = array(
        'connection' => 'amqp://guest:guest@127.0.0.1:5682/celery',
    );

    /**
     * @param array $options
     * @return $this|AbstractConnector
     * @throws \Rhubarb\Exception\ConnectionException
     * @throws \UnexpectedValueException
     */
    public function setOptions(array $options)
    {
        if (isset($options['connection'])) {
            if ($options['connection'] instanceof \AMQPConnection) {
                $this->setConnection($options['connection']);
            } elseif (is_string($options['connection'])) {
                $this->options['connection'] = $options['connection'];
                return $this->setOptions($this->parseUri($options['connection']));
            } elseif (is_array($options['connection'])) {
                unset($this->options['connection']);
                $this->options['connection'] = array();
                $connection = $options['connection'];
                if (isset($connection['host'])) {
                    $this->options['connection']['host'] = $connection['host'];
                }
                if (isset($connection['port'])) {
                    $this->options['connection']['port'] = (int)$connection['port'];
                }
                if (isset($connection['vhost'])) {
                    $this->options['connection']['vhost'] = preg_replace('#^/#', null, $connection['vhost']);
                }
                if (isset($connection['login'])) {
                    $this->options['connection']['login'] = $connection['login'];
                }
                if (isset($connection['password'])) {
                    $this->options['connection']['password'] = $connection['password'];
                }
                if (isset($connection['connect_timeout'])) {
                    $this->options['connection']['connect_timeout'] = $connection['connect_timeout'];
                }
                if (isset($connection['write_timeout'])) {
                    $this->options['connection']['write_timeout'] = $connection['write_timeout'];
                }
                if (isset($connection['read_timeout'])) {
                    $this->options['connection']['read_timeout'] = $connection['read_timeout'];
                }
            } else {
                throw new ConnectionException(
                    sprintf(
                        'options passed to %s are invalid [uri:string, array, or \AMQPConnection] expected',
                        __METHOD__
                    )
                );
            }
        }
        return $this;
    }


    /**
     * @return \AMQPConnection
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $options = $this->getOptions();
            if (is_string($options['connection'])) {
                $this->setOptions($options);
                $options = $this->getOptions();
            }
            $connection = new \AMQPConnection($options['connection']);
            $this->setConnection($connection);
        }
        return $this->connection;
    }

    /**
     * @param \AMQPConnection $connection
     * @return $this
     */
    public function setConnection(\AMQPConnection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @return bool
     */
    public function disconnect()
    {
        return $this->getConnection()->disconnect();
    }

    /**
     * @return bool
     */
    public function connect()
    {
        return $this->getConnection()->connect();
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->getConnection()->isConnected();
    }

    /**
     * @return bool
     */
    public function reconnect()
    {
        return $this->getConnection()->reconnect();
    }
}
