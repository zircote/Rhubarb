<?php
namespace Rhubarb;

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
 * @category    Rhubarb
 */
use  Rhubarb\Exception\Exception;

/**
 * This is the primary class for the utilization of the Rhubarb implementation. It allows a user to create Task objects
 * and subsequently submit these tasks to a celery cluster for asynchronous execution.
 *
 * @package     Rhubarb
 * @category    Rhubarb
 *
 */
class Rhubarb
{
    const VERSION = '3.2-dev';
    /**
     * @var string
     */
    const RHUBARB_USER_AGENT = 'rhubarb';
    /**
     * @var string
     */
    const RHUBARB_DEFAULT_TASK_QUEUE = 'celery';
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
    const RHUBARB_DEFAULT_CONTENT_ENCODING = null;
    /**
     * @var string
     */
    const RHUBARB_CONTENT_TYPE = 'application/json';
    /**
     * @var string
     */
    const RHUBARB_VERSION = '@package_version@';
    /**
     * @var string
     */
    const RHUBARB_DEFAULT_EXCHANGE_NAME = 'celery';
    /**
     * @var string
     */
    const RHUBARB_RESULTS_EXCHANGE_NAME = 'celery';
    /**
     * @var string
     */
    const RHUBARB_BROKER_NAMESPACE = '\\Rhubarb\\Broker';
    /**
     * @var string
     */
    const RHUBARB_RESULTSTORE_NAMESPACE = '\\Rhubarb\\ResultStore';
    /**
     * @var string
     */
    const NS_SEPERATOR = '\\';

    /**
     * The Result store object which provides the interface to receive the result messages from the celery cluster.
     * @var Broker\BrokerInterface
     */
    protected $resultBroker;

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
     * Accepts a single argument of configuration options as an array.
     *
     * <code>
     *  <?php
     *  $options = array(
     *      'broker' => array(
     *          'type' => 'Amqp',
     *          'options' => array(
     *              'uri' => 'amqp://celery:celery@localhost:5672/celery'
     *          )
     *      ),
     *      'result_store' => array(
     *          'type' => 'Amqp',
     *          'options' => array(
     *              'uri' => 'amqp://celery:celery@localhost:5672/celery_results'
     *          )
     *      )
     *  );
     *  $rhubarb = new \Rhubarb\Rhubarb($options);
     * </code>
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
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
     * @param array $options
     *
     * @return Rhubarb
     * @throws Exception
     */
    public function setBroker(array $options)
    {
        $namespace = self::RHUBARB_BROKER_NAMESPACE;
        if (isset($options['class_namespace']) && $options['class_namespace']) {
            $namespace = $options['class_namespace'];
        }
        $namespace = rtrim($namespace, self::NS_SEPERATOR);
        $brokerClass = $namespace . self::NS_SEPERATOR . $options['type'];
        if (!class_exists($brokerClass)) {
            throw new Exception(
                sprintf('Broker class [%s] unknown', $brokerClass)
            );
        }
        $reflect = new \ReflectionClass($brokerClass);
        $this->broker = $reflect->newInstanceArgs($this, array((array)@$options['options']));

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
     * @param array $options
     *
     * @return Rhubarb
     * @throws Exception
     */
    public function setResultStore(array $options)
    {
        if (!isset($options['type'])) {
            return $this;
        }

        $namespace = self::RHUBARB_RESULTSTORE_NAMESPACE;
        if (isset($options['class_namespace']) && $options['class_namespace']) {
            $namespace = $options['class_namespace'];
        }

        $namespace = rtrim($namespace, self::NS_SEPERATOR);
        $resultStoreClass = $namespace . self::NS_SEPERATOR . $options['type'];
        if (!class_exists($resultStoreClass)) {
            throw new Exception(
                sprintf('ResultStore class [%s] unknown', $resultStoreClass)
            );
        }

        $reflect = new \ReflectionClass($resultStoreClass);
        $this->resultBroker = $reflect->newInstanceArgs($this, array((array)@$options['options']));
        return $this;
    }

    /**
     * Returns the result store object if created otherwise false.
     *
     * @return \Rhubarb\ResultStore\ResultStoreInterface
     */
    public function getResultStore()
    {
        if ($this->resultBroker) {
            return $this->resultBroker;
        }
        return false;
    }

    /**
     * Prepares a new Task object and returns it for delay utilization
     *
     * @param string $name
     * @param array $args
     *
     * @return Task
     */
    public function sendTask($name, array $args)
    {
        $task = new Task($name, $args, $this);
        return $task;
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
     * @return null|array|string|int
     */
    public function getOption($name)
    {
        $name = strtolower($name);
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }
        return null;
    }

    /**
     * Allows the implementer to specify a single top level option at runtime.
     *
     * @param string $name
     * @param mixed $value
     *
     * @return self
     * @throws Exception
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            throw new Exception('invalid options name');
        }
        $name = strtolower($name);
        if ($name == 'result_store') {
            $this->options['result_store'] = $value;
            $this->setResultStore($value);
        } elseif ($name == 'broker') {
            $this->options['broker'] = $value;
            $this->setBroker($value);
        } elseif ($name == 'logger') {
            $this->options['logger'] = $value;
        }
        return $this;
    }
}
