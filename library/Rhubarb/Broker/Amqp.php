<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
use Rhubarb\Connector\Amqp as AmqpConnector;
use Rhubarb\Tasks;
use Rhubarb\Rhubarb;
use AMQP\Message as AmqpMessage;
use Rhubarb\Message;

/**
 * @package     Rhubarb
 * @category    Broker
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
 *      ...
 * );
 * $rhubarb = new \Rhubarb\Rhubarb($options);
 */
class Amqp extends AmqpConnector implements BrokerInterface
{


    /**
     * @param \Rhubarb\Task $task
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        if (!$task->getMessage()->getPropRoutingKey()) {
            $task->getMessage()->setPropRoutingKey($task->getId());
        }
        $channel = $this->getConnection()->channel();
        $channel->queueDeclare(
            array(
                'queue' => $task->getMessage()->getQueue(),
                'durable' => $task->getMessage()->getPropDurable(),
                'auto_delete' => $task->getMessage()->getPropAutoDelete(),
                'arguments' => $task->getMessage()->getPropQueueArgs()
            )
        );
        
        $channel->exchangeDeclare(
            $task->getMessage()->getPropExchange(),
            'direct',
            array('passive' => true, 'durable' => true)
        );
        
        $channel->queueBind(
            $task->getMessage()->getQueue(),
            $task->getMessage()->getPropExchange(),
            array('routing_key' => $task->getMessage()->getPropRoutingKey())
        );
        
        $msgProperties =  array(
            'content_type' => Rhubarb::RHUBARB_CONTENT_TYPE,
            'content_encoding' => $task->getMessage()->getContentEncoding(),
            'priority' => $task->getMessage()->getPropPriority()
        );

        $channel->basicPublish(
            new AmqpMessage((string) $task, $msgProperties),
            array(
                'exchange' => $task->getMessage()->getPropExchange(),
                'routing_key' => $task->getMessage()->getPropRoutingKey()
            )
        );
        
        $channel->close();
        $channel = null;
    }
}
