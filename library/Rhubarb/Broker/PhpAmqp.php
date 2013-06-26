<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
use AMQPConnection;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
use Rhubarb\Exception\Exception;
use Rhubarb\Rhubarb;
use Rhubarb\Task;

/**
 * @package     Rhubarb
 * @category    Broker
 *
 * Use:
 *
 * $options = array(
 *  'broker' => array(
 *      'type' => 'PhpAmqp',
 *      'options' => array(
 *          'uri' => 'amqp://celery:celery@localhost:5672/celery'
 *      )
 *  ),
 *  'result_store' => array(
 *      ...
 * );
 * $rhubarb = new \Rhubarb\Rhubarb($options);
 */
class PhpAmqp extends AbstractBroker
{
    protected $exchange = Rhubarb::RHUBARB_DEFAULT_EXCHANGE_NAME;

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
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @param Task $task
     * @throws \Rhubarb\Exception\Exception
     */
    public function publishTask(Task $task)
    {
        $connection = $this->getConnection();
        $connection->connect();
        $channel = new AMQPChannel($connection);
        
        if (!$channel->isConnected()) {
            throw new Exception('AMQP Failed to Connect');
        }
        $queue = new AMQPQueue($channel);
        
        $queue->setName($this->message['properties']['name']);
        $queue->setFlags(AMQP_DURABLE);
        if ($this->options['options']) {
            $queue->setArguments($this->options['options']);
        }
        $queue->declareQueue();
        
        $exchange = new AMQPExchange($channel);
        $exchange->setFlags(AMQP_PASSIVE|AMQP_DURABLE);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setName($this->exchange);
        $exchange->declareExchange();
        $queue->bind($this->exchange, $task->getId());
        
        $msgProperties = array(
            'content_type' => Rhubarb::RHUBARB_CONTENT_TYPE
        );
        
        if ($task->getPriority()) {
            $msgProperties['priority'] = $task->getPriority();
        }
        $exchange->publish(
            (string)$task, 
            $task->getId(), 
            AMQP_NOPARAM,
            $msgProperties
        );
        $this->getConnection()->disconnect();
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
