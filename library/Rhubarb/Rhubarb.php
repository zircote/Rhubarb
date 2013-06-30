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
 * @package     Rhubarb
 * @category    Rhubarb
 *
 * Use:
 *
 * $options = array(
 *  'broker' => array(
 *      'type' => 'Amqp',
 *      'options' => array(
 *          'uri' => 'amqp://celery:celery@localhost:5672/celery'
 *      )
 *  ),
 *  'result_store' => array(
 *      'type' => 'Amqp',
 *      'options' => array(
 *          'uri' => 'amqp://celery:celery@localhost:5672/celery_results'
 *      )
 *  )
 * );
 * $rhubarb = new \Rhubarb\Rhubarb($options);
 */
class Rhubarb
{
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
    const RHUBARB_DEFAULT_CONTENT_ENCODING = 'utf-8';
    /**
     * @var string
     */
    const RHUBARB_DEFAULT_BODY_ENCODING = 'base64';
    /**
     * @var string
     */
    const RHUBARB_CONTENT_TYPE = 'application/json';
    /**
     * @var string
     */
    const RHUBARB_VERSION = '0.0.4';
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
     * @var Broker\BrokerInterface
     */
    protected $resultBroker;

    /**
     * @var Broker\BrokerInterface
     */
    protected $broker;
    protected $options
        = array(
            'broker'       => array(),
            'result_store' => array()
        );

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @param $options
     *
     * @return Rhubarb
     * @throws Exception
     */
    public function setBroker($options)
    {
        $namespace = self::RHUBARB_BROKER_NAMESPACE;
        if (isset($options['class_namespace']) && $options['class_namespace']) {
            $namespace = $options['class_namespace'];
        }
        $namespace = rtrim($namespace,self::NS_SEPERATOR);
        $brokerClass = $namespace . self::NS_SEPERATOR . $options['type'];
        if (!class_exists($brokerClass)) {
            throw new Exception(
                sprintf('Broker class [%s] unknown', $brokerClass)
            );
        }
        $reflect = new \ReflectionClass($brokerClass);
        $this->broker = $reflect->newInstanceArgs(array((array)@$options['options']));

        return $this;
    }

    /**
     * @return \Rhubarb\Broker\BrokerInterface
     */
    public function getBroker()
    {
        return $this->broker;
    }

    /**
     * @param $options
     *
     * @return Rhubarb
     * @throws Exception
     */
    public function setResultStore($options)
    {
        if (!isset($options['type'])) {
            return $this;
        }
        $namespace = self::RHUBARB_RESULTSTORE_NAMESPACE;
        if (isset($options['class_namespace'])&& $options['class_namespace']) {
            $namespace = $options['class_namespace'];
        }
        $namespace = rtrim($namespace,self::NS_SEPERATOR);
        $resultStoreClass = $namespace . self::NS_SEPERATOR . $options['type'];
        if (!class_exists($resultStoreClass)) {
            throw new Exception(
                sprintf('ResultStore class [%s] unknown', $resultStoreClass)
            );
        }
        $reflect = new \ReflectionClass($resultStoreClass);
        $this->resultBroker = $reflect->newInstanceArgs(array((array)@$options['options']));
        return $this;
    }

    /**
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
     * @param $name
     * @param $args
     *
     * @return Task
     */
    public function sendTask($name, $args)
    {
        $task = new Task($name, $args, $this);
        return $task;
    }

    /**
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
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param $name
     * @param $value
     *
     * @throws Exception
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            throw new Exception('invalid options name');
        }
        $name = strtolower($name);
        if ($name == 'result_store') {
            $value = array_merge($this->options['result_store'], $value);
            $this->setResultStore($value);
        } elseif ($name == 'broker') {
            $value = array_merge($this->options['broker'], $value);
            $this->setBroker($value);
        } else {
            if (array_key_exists($name, $this->options)) {
                $this->_setOption($name, $value);
            }
        }
    }

    /**
     * @param $name
     *
     * @return null
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
     * @param $name
     * @param $value
     */
    private function _setOption($name, $value)
    {
        if (is_string($name) && array_key_exists($name, $this->options)) {
            $this->options[$name] = $value;
        }
    }
}
