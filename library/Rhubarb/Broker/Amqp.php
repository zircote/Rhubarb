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
        $channel = $this->getConnection()->channel();
        //  array('x-ha-policy' => array('S', 'all')
        $channel->queueDeclare(
            array('queue' => $task->message->getQueue(), 'durable' => true,'auto_delete' => false,
                  'arguments' => array('x-ha-policy' => array('S', 'all')))
        );
        $channel->exchangeDeclare($task->message->getPropExchange(), 'direct',array('passive' => true, 'durable' => true));
        $channel->queueBind('celery', $task->message->getPropExchange(), array('routing_key' => $task->getId()));
        
        $msgProperties =  array('content_type' => Rhubarb::RHUBARB_CONTENT_TYPE);
        if($task->getPriority()){
            $msgProperties['priority'] = $task->getPriority();
        }

        $task->message->setPropBodyEncoding(null);
        $taskArray = $task->toArray();
        
        $channel->basicPublish(
            new AmqpMessage(json_encode($taskArray['body']), $msgProperties),
            array('exchange' => $task->message->getPropExchange(), 'routing_key' => $task->getId())
        );
        
        $channel->close();
        $channel = null;
    }
}
