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
        $task->getMessage()->setPropBodyEncoding(Message::BODY_ENCODING_BASE64);
        $this->getConnection()->lpush($task->getMessage()->getPropExchange(), (string) $task);
    }
}
