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
use \Rhubarb\Rhubarb;

/**
 * @package
 * @category
 * @subcategory
 */
class AbstractConnector implements ConnectorInterface
{

    const AMQP_PERSISTENT = 2;
    const AMQP_NON_PERSISTENT = 1;
    /**
     * @var array
     */
    protected $options = array();
    /**
     * @var Rhubarb
     */
    protected $rhubarb;
    /**
     * @var array
     */
    protected $headers = array();
    /**
     * @var array
     */
    protected $properties = array(
        'content_type' => Rhubarb::CONTENT_TYPE_JSON,
        'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
        'delivery_mode' => self::AMQP_PERSISTENT,
        'priority' => 0
    );


    /**
     * @param \Rhubarb\Rhubarb $rhubarb
     * @param array $options
     */
    public function __construct(Rhubarb $rhubarb, array $options = array())
    {
        $this->setOptions($options);
        $this->setRhubarb($rhubarb);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     * @throws \UnexpectedValueException
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param mixed $rhubarb
     * @return AbstractConnector
     */
    public function setRhubarb(Rhubarb $rhubarb)
    {
        $this->rhubarb = $rhubarb;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRhubarb()
    {
        return $this->rhubarb;
    }

    /**
     * @param $connection
     * @return array
     * @throws ConnectionException
     */
    public function parseUri($connection)
    {
        $uri = parse_url($connection);
        $connection = array();
        if (!isset($uri['port'])) {
            /* This is shit, it will be refactored out */
            switch ($uri['scheme']) {
                case 'redis':
                    $uri['port'] = 6379;
                    break;
                case 'amqp':
                    $uri['port'] = 5672;
                    break;
                case 'amqps':
                    throw new ConnectionException('AMQP via TLS is not supported currently by ext-amqp');
                    break;
                default:
                    throw new ConnectionException('unknown URI scheme provided [redis:// or amqp://] expected');
            }
        } else {
            $connection['port'] = (integer)$uri['port'];
        }
        $connection['host'] = $uri['host'];
        $connection['vhost'] = isset($uri['path']) ? $uri['path'] : null;
        /* 
         * I don't like it but to ensure that all parties are happy this is necessary
         * PECL-AMQP does whacky shit with the leading `/` in the vhost that is it seems to always prepend it
         * to whatever you pass in however rabbit-mq will allows a vhost with and without. Most libraries are quite
         * happy to treat `/celery` as `celery` as seen in the rabbit-mq run-time.
         */
        $connection['vhost'] = preg_replace('#^/#', null, $connection['vhost']);
        $connection['login'] = isset($uri['user']) ? $uri['user'] : null;
        $connection['password'] = isset($uri['pass']) ? $uri['pass'] : null;
        if (isset($uri['query'])) {
            $query = array();
            parse_str($uri['query'], $query);
            $connection = array_merge($connection, $query);
        }
        return array('connection' => $connection);
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
 
