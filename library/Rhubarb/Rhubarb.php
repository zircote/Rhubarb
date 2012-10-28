<?php
namespace Rhubarb;

/**
 * @package
 * @category
 * @subcategory
 */
use AMQP\Connection;

/**
 * @package
 * @category
 * @subcategory
 */
class Rhubarb
{
    const RHUBARB_USER_AGENT = 'rhubarb';
    const RHUBARB_VERSION = '0.0.4';
    const RHUBARB_DEFAULT_EXCHANGE = 'celery';
    const RHUBARB_RESULTS_EXCHANGE = 'celeryresults';

    /**
     * @var \AMQP\Connection
     */
    protected $connection;
    protected $exchange;
    protected $options = array(
        'celery' => array(
            'exchange' => self::RHUBARB_DEFAULT_EXCHANGE,
            'results_exchange' => self::RHUBARB_RESULTS_EXCHANGE
        )
    );

    /**
     * @param array $options
     *
     *
     * $options = array(
     *     'amqp' => array(),
     *     'celery' => array(
     *         'exchange' => self::RHUBARB_DEFAULT_EXCHANGE
     *      )
     *  );
     */
    public function __construct(array $options=array())
    {
        if($options){
            $this->options = array_merge($this->options,$options);
        }
        $this->setConnection($options);
    }

    /**
     * @param $options
     *
     * @see \AMQP\Connection for option details
     *
     * @return Rhubarb
     */
    public function setConnection($options)
    {
        if(isset($options['amqp']['connection']) && $options['amqp']['connection'] instanceof \AMQP\Connection){
            $this->connection = $options['amqp']['connection'];
        } else {
            $this->connection = new \AMQP\Connection($options['amqp']['uri'], @$options['amqp']['uri'] ?: array());
        }
        return $this;
    }
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return \AMQP\Connection
     */
    public function getConnection()
    {
        if(!$this->connection){
            $this->setConnection($this->options);
        }
        return $this->connection;
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
}
