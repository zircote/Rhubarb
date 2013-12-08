<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
use Rhubarb\Connector\Predis as PredisConnection;
use Rhubarb\Message;
use Rhubarb\Rhubarb;
use Rhumsaa\Uuid\Uuid;

/**
 * @package     Rhubarb
 * @category    Broker
 */
class Predis extends PredisConnection implements BrokerInterface
{
    /**
     * @param \Rhubarb\Task $task
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        $task->getMessage()->setContentEncoding(Rhubarb::CONTENT_ENCODING_UTF8);
        if (!$task->getMessage()->getPropRoutingKey()) {
            $task->getMessage()->setPropRoutingKey('celery');
        }
        if (!$task->getMessage()->getCorrelationId()){
            $task->getMessage()->setCorrelationId($task->getId());
        }
        if (!$task->getMessage()->getReplyTo()){
            $task->getMessage()->setReplyTo($task->getId());
        }
        $task->getMessage()->setPropDeliveryMode(2)->setPropDeliveryTag(2);
        $task->getMessage()->setBodyEncoding(Rhubarb::CONTENT_ENCODING_BASE64);
        $task->toArray();
        $this->getConnection()->lpush($task->getMessage()->getPropExchange(), (string) $task->getMessage());
    }
}
