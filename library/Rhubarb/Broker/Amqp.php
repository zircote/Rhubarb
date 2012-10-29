<?php
namespace Rhubarb\Broker;

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
class Amqp implements BrokerInterface
{

    /**
     * @var \AMQP\Connection
     */
    protected $connection;
    protected $exchange = \Rhubarb\Rhubarb::RHUBARB_DEFAULT_EXCHANGE_NAME;
    protected $resultsExchange = \Rhubarb\Rhubarb::RHUBARB_RESULTS_EXCHANGE_NAME;
    /**
     * @var array
     */
    protected $options = array(
        'uri' => 'amqp://guest:guest@localhost:5672/',
        'options' => array()
    );

    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    public function publishTask(\Rhubarb\Task $task)
    {
        $channel = $this->getConnection()->channel();
        $channel->exchangeDeclare($this->exchange, 'direct', true, true);
        $channel->queueBind('celery', $this->exchange, $task->getId());
        $channel->basicPublish(
            new \AMQP\Message((string) $task, array('content_type' => 'application/json')),
            $this->exchange,
            $task->getId()
        );
        $channel->close();
        $channel = null;
    }
    public function getTaskResult(\Rhubarb\Task $task)
    {
        try {
            $result = false;
            $channel = $this->getConnection()->channel();
            if($message = $channel->basicGet($task->getId())){
                $channel->basicAck($message->delivery_info['delivery_tag']);
                $channel->queueDelete($task->getId());
                $result = json_decode($message->body);
            }
            $channel->close();
            return $result;
        } catch (\AMQP\Exception\ChannelException $e){
            return $result;
        }
    }

    /**
     * @return \AMQP\Connection
     */
    public function getConnection()
    {
        if(!$this->connection){
            $options = $this->getOptions();
            $connection = new \AMQP\Connection($options['uri'], @$options['options'] ?: array());
            $this->setConnection($connection);
        }
        return $this->connection;
    }

    /**
     * @param \AMQP\Connection $connection
     *
     * @return AMQP
     */
    public function setConnection(\AMQP\Connection $connection)
    {
        $this->connection = $connection;
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
     * @param array $options
     *
     * @return AMQP
     */
    public function setOptions(array $options)
    {
        if(isset($options['exchange'])){
            $this->exchange = $options['exchange'];
        }
        if(isset($options['results_exchange'])){
            $this->resultsExchange = $options['results_exchange'];
        }
        $merged = array('uri' => isset($options['uri']) ? $options['uri'] : $this->options['uri']);
        $merge['options'] = array_merge($this->options['options'], (array) @$options['options']);
        $this->options = $merged;
        return $this;
    }
}
