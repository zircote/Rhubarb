<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
use Rhubarb\Connector\PhpAmqp as PhpAmqpConnector;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
use Rhubarb\Exception\Exception;
use Rhubarb\Message;
use Rhubarb\Rhubarb;

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
class PhpAmqp extends PhpAmqpConnector implements BrokerInterface
{
    
    /**
     * @param \Rhubarb\Task $task
     * @throws \Rhubarb\Exception\Exception
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        $msg = new Message();
        $connection = $this->getConnection();
        $connection->connect();
        $channel = new AMQPChannel($connection);
        
        if (!$channel->isConnected()) {
            throw new Exception('AMQP Failed to Connect');
        }
        $queue = new AMQPQueue($channel);
        
        $queue->setName($msg->getPropName());
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
}
