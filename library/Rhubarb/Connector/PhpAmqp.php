<?php
namespace Rhubarb\Connector;

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
 * @package
 * @category
 * @subcategory
 */
use AMQPConnection;

/**
 * @package
 * @category
 * @subcategory
 */
class PhpAmqp extends AbstractConnector
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


    /**
     * @param array $options
     *
     * @return self
     *
     * @throws \UnexpectedValueException
     */
    public function setOptions(array $options)
    {
        if (isset($options['exchange'])) {
            $this->exchange = $options['exchange'];
        }
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
        /*
         * @deprecated use of $options['uri'] is deprecated in favor of $options['connection']
         * this block is meant to provide reverse comparability 
         */
        if (!isset($options['connection']) && isset($options['uri'])) {
            trigger_error(
                'the use of `uri` in config options is no longer support for Rhubarb version >= 3.1',
                E_USER_DEPRECATED
            );
            $options['connection'] = $options['uri'];
            unset($options['connection']);
        } elseif (isset($options['uri'])) {
            trigger_error('as of Rhubarb version >= 3.1 `uri` config options is unsupported, you must not implement ' .
                '`connection` and `uri` together', E_NOTICE);
            unset($options['connection']);
        }
        if (isset($options['connection'])) {
            $uri = parse_url($options['connection']);
            if (!isset($uri['port'])) {
                $uri['scheme'] == 'amqps' ? 5673 : $this->options['connection']['port'];
            } else {
                $port = isset($uri['port']) ? $uri['port'] : $this->options['connection']['port'];
            }
            unset($options['connection']);
            $options['connection']['host'] = $uri['host'];
            $options['connection']['port'] = $port;
            $options['connection']['vhost'] = isset($uri['path']) ? $uri['path'] : $this->options['connection']['path'];
            /* I don't like it but to ensure that all parties are happy this is necasary */
            $options['connection']['vhost'] = preg_replace('#^/#', null, $options['connection']['vhost']);
            $options['connection']['login'] = isset($uri['username']) ? $uri['username'] : $this->options['connection']['login'];
            $options['connection']['password'] = isset($uri['pass']) ? $uri['pass'] : $this->options['connection']['password'];
            $this->options['connection'] = $options['connection'];
        }
        return $this;
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
     * @return self
     */
    public function setConnection(AMQPConnection $connection)
    {
        $this->connection = $connection;
        return $this;
    }
}
