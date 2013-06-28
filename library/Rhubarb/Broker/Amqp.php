<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
use Rhubarb\Connector\Amqp as AmqpConnector;
use Rhubarb\Tasks;
use Rhubarb\Rhubarb;
use AMQP\Message;

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

    protected $message = null;

    /**
     * @param Task $task
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        $channel = $this->getConnection()->channel();
        $channel->queueDeclare(
            array('queue' => 'celery', 'durable' => true,'auto_delete' => false,
                  'arguments' => array('x-ha-policy' => array('S', 'all')))
        );
        $channel->exchangeDeclare($this->exchange, 'direct',array('passive' => true, 'durable' => true));
        $channel->queueBind('celery', $this->exchange, array('routing_key' => $task->getId()));
        
        $msgProperties =  array('content_type' => Rhubarb::RHUBARB_CONTENT_TYPE);
        if($task->getPriority()){
            $msgProperties['priority'] = $task->getPriority();
        }

        $channel->basicPublish(
            new Message((string) $task, $msgProperties),
            array('exchange' => $this->exchange, 'routing_key' => $task->getId())
        );
        
        $channel->close();
        $channel = null;
    }
}
