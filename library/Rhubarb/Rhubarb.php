<?php
namespace Rhubarb;

/**
 * @package
 * @category
 * @subcategory
 */

/**
 * @package
 * @category
 * @subcategory
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
 *  'result_broker' => array(
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
    const RHUBARB_USER_AGENT = 'rhubarb';
    const RHUBARB_VERSION = '0.0.4';
    const RHUBARB_DEFAULT_EXCHANGE_NAME = 'celery';
    const RHUBARB_RESULTS_EXCHANGE_NAME = 'celeryresults';
    const RHUBARB_BROKER_NAMESPACE = '\\Rhubarb\\Broker\\';

    /**
     * @var Broker\BrokerInterface
     */
    protected $resultBroker;

    /**
     * @var Broker\BrokerInterface
     */
    protected $broker;
    protected $options = array(
        'exchange' => self::RHUBARB_DEFAULT_EXCHANGE_NAME,
        'results_exchange' => self::RHUBARB_RESULTS_EXCHANGE_NAME
    );

    public function __construct(array $options = array())
    {
        $this->setOptions(array_merge($this->options, $options));
    }

    /**
     * @param $options
     *
     * @return Rhubarb
     * @throws Exception\Exception
     */
    public function setBroker($options)
    {
        $brokerClass = self::RHUBARB_BROKER_NAMESPACE . $options['type'];
        if(!class_exists($brokerClass)){
            throw new \Rhubarb\Exception\Exception(
                sprintf('Broker class [%s] unknown',$brokerClass)
            );
        }
        $reflect = new \ReflectionClass($brokerClass);
        $this->broker = $reflect->newInstanceArgs(array((array) @$options['options']));

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
     * @throws Exception\Exception
     */
    public function setResultBroker($options)
    {
        $brokerClass = self::RHUBARB_BROKER_NAMESPACE . $options['type'];
        if(!class_exists($brokerClass)){
            throw new \Rhubarb\Exception\Exception(
                sprintf('Broker class [%s] unknown',$brokerClass)
            );
        }
        $reflect = new \ReflectionClass($brokerClass);
        $this->resultBroker = $reflect->newInstanceArgs(array($options['options']));
        return $this;
    }

    /**
     * @return \Rhubarb\Broker\BrokerInterface
     */
    public function getResultBroker()
    {
        if($this->resultBroker instanceof Broker\BrokerInterface){
            return $this->resultBroker;
        }
        return $this->broker;
    }

    /**
     * @param $name
     * @param $args
     *
     * @return Result\AsynchResult
     */
    public function sendTask($name, $args)
    {
        $task = new Task($name, $args, $this);
        return $task->applyAsync();
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
     * @throws Exception\Exception
     */
    public function setOption($name, $value)
    {
        if (!is_string($name)) {
            throw new \Rhubarb\Exception\Exception('invalid options name');
        }
        $name = strtolower($name);
        if ($name == 'result_broker') {
            $this->setResultBroker($value);
        } elseif ($name == 'broker') {
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
