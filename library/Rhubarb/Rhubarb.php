<?php
namespace Rhubarb;

/**
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
 * @category    Rhubarb
 */
use Rhubarb\Broker\BrokerInterface;
use Rhubarb\Exception\Exception;
use Rhubarb\Exception\MessageUnserializeException;
use Rhubarb\Exception\TaskSignatureException;
use Rhubarb\Task\AsyncResult;
use Rhubarb\Message\Message;
use Rhubarb\Task\Body\BodyInterface;
use Rhubarb\ResultStore\ResultStoreInterface;
use Rhubarb\Task\Signature;
use Rhubarb\Exception\EncodingException;

/**
 * This is the primary class for the utilization of the Rhubarb implementation. It allows a user to create AsyncResult
 * objects and subsequently submit these tasks to a celery cluster for asynchronous execution.
 *
 * @package     Rhubarb
 * @category    Rhubarb
 *
 */
class Rhubarb
{
    /**
     * @var string
     */
    const USER_AGENT = 'rhubarb';
    /**
     * @var string
     */
    const VERSION = '3.2-dev';
    /**
     * @var string
     */
    const CONTENT_ENCODING_BASE64 = 'base64';
    /**
     * @var string
     */
    const CONTENT_ENCODING_UTF8 = 'utf-8';
    /**
     * @var string
     */
    const CONTENT_ENCODING_RAW = 'raw';
    /**
     * @var string
     */
    const CONTENT_TYPE_JSON = 'application/json';
    /**
     * @var string
     */
    const CONTENT_TYPE_PROTOBUF = 'application/x-protobuf';
    /**
     * @var string
     */
    const CONTENT_TYPE_YAML = 'application/x-yaml';
    /**
     * @var string
     */
    const CONTENT_TYPE_MSGPACK = 'application/x-msgpack';
    /**
     * @var string
     */
    const CONTENT_TYPE_PICKLE = 'application/x-python-serialize';
    /**
     * @var string
     */
    const NS_SEPERATOR = '\\';
    /**
     * @var string
     */
    const BROKER_NAMESPACE = '\Rhubarb\Broker';
    /**
     * @var string
     */
    const RESULTSTORE_NAMESPACE = '\Rhubarb\ResultStore';
    /**
     * @var string
     */
    const DEFAULT_TASK_QUEUE = 'celery';
    /**
     * @var string
     */
    const RESULTS_EXCHANGE_NAME = 'celeryresults';
    /**
     * @var string
     */
    const EVENTS_EXCHANGE_NAME = 'celeryev';
    /**
     *
     */
    const CELERY_DIRECT_WORKER_QUEUE = 'C.dq';
    /**
     *
     */
    const KOMBU_BINDING_CELERY = '_kombu.binding.celery';
    /**
     * @var string
     */
    const DEFAULT_EXCHANGE_NAME = 'celery';
    /**
     * @var string
     */
    const DEFAULT_CONTENT_TYPE = self::CONTENT_TYPE_JSON;
    /**
     * @var string
     */
    const DEFAULT_CONTENT_ENCODING = self::CONTENT_ENCODING_UTF8;
    /**
     *  JSON_BIGINT_AS_STRING|JSON_NUMERIC_CHECK|JSON_UNESCAPED_SLASHES
     * @var int
     */
    static public $jsonOptions;
    /**
     * @var array
     */
    protected $taskRegistry = array();

    /**
     * The Result store object which provides the interface to receive the result messages from the celery cluster.
     *
     * @var ResultStore\ResultStoreInterface
     */
    protected $resultStore;

    /**
     * This broker object is responsible for the delivery of tasks and arguments to the celery cluster for execution.
     *
     * @var Broker\BrokerInterface
     */
    protected $broker;

    /**
     * @var \Logger
     */
    protected $logger;
    /**
     * @var array
     */
    private $decoders = array();
    /**
     * @var array
     */
    private $encoders = array();
    /**
     * @var array
     */
    private $serializers = array();
    /**
     * @var array
     */
    private $unserializers = array();

    /**
     * The default configuration for the Rhubarb interface.
     *
     * @var array
     */
    protected $options
        = array(
            'broker' => array(),
            'result_store' => array(),
            'logger' => array(
                'loggers' => array(
                    'dev' => array(
                        'level' => 'DEBUG',
                        'appenders' => array(__NAMESPACE__),
                    ),
                ),
                'appenders' => array(
                    __NAMESPACE__ => array(
                        'class' => 'LoggerAppenderNull'
                    )
                )
            )
        );
    /**
     * @var array
     */
    protected $state = array();

    /**
     * Accepts a single argument of configuration options as an array.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        /* I would prefer this was a class constant; however PHP will not allow bitwise assignments in the class body */
        static::$jsonOptions = JSON_BIGINT_AS_STRING | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES;
        $this->serializers[self::CONTENT_TYPE_JSON] = function ($payload) {
            return json_encode($payload, static::$jsonOptions);
        };
        $this->unserializers[self::CONTENT_TYPE_JSON] = function ($payload) {
            return json_decode($payload, static::$jsonOptions);
        };
        $this->encoders[self::CONTENT_ENCODING_BASE64] = function ($payload) {
            return base64_encode($payload);
        };
        $this->decoders[self::CONTENT_ENCODING_BASE64] = function ($payload) {
            return base64_decode($payload);
        };
        $this->encoders[self::CONTENT_ENCODING_UTF8] = function ($payload) {
            /* Don't take any action for now */
            return $payload;
        };
        $this->decoders[self::CONTENT_ENCODING_UTF8] = function ($payload) {
            /* Don't take any action for now */
            return $payload;
        };
        $this->setOptions($options);
        \Logger::configure($this->getOption('logger'));
    }

    /**
     * @return \Logger
     */
    public function getLogger()
    {
        return \Logger::getLogger(__NAMESPACE__);
    }

    /**
     * Accepts an array of options enabling the declaration and instantiation of the broker object.
     *
     * @param array|BrokerInterface $broker
     *
     * @return Rhubarb
     * @throws \InvalidArgumentException
     */
    public function setBroker($broker)
    {
        if ($broker instanceof BrokerInterface) {
            $this->broker = $broker;
        } elseif (is_array($broker)) {
            $this->options['broker'] = $broker;
            $namespace = self::BROKER_NAMESPACE;
            if (isset($broker['class_namespace']) && $broker['class_namespace']) {
                $namespace = $broker['class_namespace'];
            }
            $namespace = rtrim($namespace, self::NS_SEPERATOR);
            $brokerClass = $namespace . self::NS_SEPERATOR . $broker['type'];
            if (!class_exists($brokerClass)) {
                throw new \InvalidArgumentException(
                    sprintf('Broker class [%s] unknown', $brokerClass)
                );
            }
            $reflect = new \ReflectionClass($brokerClass);
            $this->broker = $reflect->newInstanceArgs(array($this, (array)@$broker['options']));
        }
        return $this;
    }

    /**
     * Return the broker
     *
     * @return \Rhubarb\Broker\BrokerInterface
     */
    public function getBroker()
    {
        return $this->broker;
    }

    /**
     * Instantiates the ResultStore object by using user supplied options. Supported options are as follows;
     *
     * - <b>type:</b> the Class Type that will be created
     * - <b>class_namespace:</b> over-ride the base namespace to a user namespace for custom result-broker classes
     * - <b>options:</b> result_broker class specific options
     *
     * @param array|ResultStoreInterface $resultStore
     *
     * @return Rhubarb
     * @throws Exception
     * @throws \InvalidArgumentException
     */
    public function setResultStore($resultStore)
    {
        if ($resultStore instanceof ResultStoreInterface) {
            $this->resultStore = $resultStore;
        } elseif (is_array($resultStore)) {
            $this->options['result_store'] = $resultStore;
            $namespace = self::RESULTSTORE_NAMESPACE;
            if (isset($resultStore['class_namespace']) && $resultStore['class_namespace']) {
                $namespace = $resultStore['class_namespace'];
            }
            $namespace = rtrim($namespace, self::NS_SEPERATOR);
            $resultStoreClass = $namespace . self::NS_SEPERATOR . $resultStore['type'];
            if (!class_exists($resultStoreClass)) {
                throw new \InvalidArgumentException(
                    sprintf('ResultStore class [%s] unknown', $resultStoreClass)
                );
            }
            $reflect = new \ReflectionClass($resultStoreClass);
            $this->resultStore = $reflect->newInstanceArgs(array($this, (array)@$resultStore['options']));
        }
        return $this;
    }

    /**
     * Returns the result store object if created otherwise false.
     *
     * @return \Rhubarb\ResultStore\ResultStoreInterface
     */
    public function getResultStore()
    {
        return $this->resultStore;
    }

    /**
     * Set the Rhubarb options from an array
     *
     * @param array $options
     *
     * @return Rhubarb
     */
    public function setOptions(array $options)
    {
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
        return $this;
    }

    /**
     * Returns the options to the user
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns the name options key
     *
     * @param string $name
     *
     * @return void|array|string|int
     */
    public function getOption($name)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }
    }

    /**
     * Allows the implementer to specify a single top level option at runtime.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('invalid options name');
        }
        $name = strtolower($name);
        if ($name == 'result_store') {
            $this->setResultStore($value);
        } elseif ($name == 'broker') {
            $this->setBroker($value);
        } elseif ($name == 'logger') {
            $this->options['logger'] = $value;
        } elseif ($name == 'tasks') {
            $this->setTasks($value);
        }
        return $this;
    }

    /**
     * @param array $tasks
     * @return $this
     */
    public function setTasks(array $tasks)
    {
        $this->options['tasks'] = $tasks;
        foreach ($tasks as $task) {
            $this->addTask($task);
        }
        return $this;
    }

    /**
     * @param $task
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addTask($task)
    {
        $taskTemplate = array(
            'name' => null,
            'properties' => array(),
            'headers' => array()
        );
        if (!isset($task['name'])) {
            throw new \InvalidArgumentException('task name must be declared in the task definition');
        }
        $taskTemplate['name'] = $task['name'];

        if (isset($task['properties'])) {
            $taskTemplate['properties'] = array_merge($taskTemplate['properties'], (array)$task['properties']);
        }
        if (isset($task['headers'])) {
            $taskTemplate['headers'] = array_merge($taskTemplate['headers'], (array)$task['headers']);
        }
        $this->taskRegistry[$task['name']] = $taskTemplate;
        return $this;
    }

    /**
     * @param $task
     */
    public function delTask($task)
    {
        unset($this->taskRegistry[$task]);
    }

    /**
     * @param Message $message
     * @return AsyncResult
     */
    public function dispatch(Message $message)
    {
        $this->state[$message->getId()] = $message;
        $this->getBroker()->publishTask($message);
        $result = new AsyncResult($this, $message);
        return $result;
    }

    /**
     * @param $name
     * @return Signature
     */
    public function t($name)
    {
        return $this->task($name);
    }

    /**
     * @throws TaskSignatureException
     * @param $name
     * @param BodyInterface $body
     * @param array $properties
     * @param array $headers
     * @return Signature
     */
    public function task($name, BodyInterface $body = null, $properties = array(), $headers = array())
    {
        if (!array_key_exists($name, $this->taskRegistry)) {
            throw new TaskSignatureException(sprintf('Task [%s] is not in registered', $name));
        }
        return new Signature(
            $this,
            $this->taskRegistry[$name]['name'],
            $body,
            array_merge((array)$this->taskRegistry[$name]['properties'], $properties),
            array_merge((array)$this->taskRegistry[$name]['headers'], $headers)
        );
    }

    /**
     * @param string $payload
     * @param string $type
     * @return mixed
     * @throws \Rhubarb\Exception\EncodingException
     */
    final public function decode($payload, $type = self::CONTENT_ENCODING_UTF8)
    {
        $type = strtolower($type);
        if (isset($this->decoders[$type]) && is_callable($this->decoders[$type])) {
            return call_user_func($this->decoders[$type], $payload);
        } else {
            throw new EncodingException(
                sprintf('failed to decode payload of type [%s] ensure it is declared in your configuration', $type)
            );
        }
    }

    /**
     * @param $payload
     * @param string $type
     * @return mixed
     * @throws \Rhubarb\Exception\EncodingException
     */
    final public function encode($payload, $type = self::CONTENT_ENCODING_UTF8)
    {
        $type = strtolower($type);
        if (isset($this->encoders[$type]) && is_callable($this->encoders[$type])) {
            return call_user_func($this->encoders[$type], $payload);
        } else {
            throw new EncodingException(
                sprintf('failed to encode payload of type [%s] ensure it is declared in your configuration', $type)
            );
        }
    }

    /**
     * @param $payload
     * @param string $type
     * @return mixed
     * @throws \Rhubarb\Exception\MessageUnserializeException
     */
    final public function serialize($payload, $type = self::CONTENT_TYPE_JSON)
    {
        $type = strtolower($type);
        if (isset($this->serializers[$type]) && is_callable($this->serializers[$type])) {
            return call_user_func($this->serializers[$type], $payload);
        } else {
            throw new MessageUnserializeException(
                sprintf('failed to serialize payload of type [%s] ensure it is declared in your configuration', $type)
            );
        }
    }

    /**
     * @param $payload
     * @param string $type
     * @return mixed
     * @throws \Rhubarb\Exception\MessageUnserializeException
     */
    final public function unserialize($payload, $type = self::CONTENT_TYPE_JSON)
    {
        $type = strtolower($type);
        if (isset($this->unserializers[$type]) && is_callable($this->unserializers[$type])) {
            return call_user_func($this->unserializers[$type], $payload);
        } else {
            throw new MessageUnserializeException(
                sprintf('failed to unserialize payload of type [%s] ensure it is declared in your configuration', $type)
            );
        }
    }
}
