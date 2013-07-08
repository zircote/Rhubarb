<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
use Rhubarb\Connector\Predis as PredisConnection;
use Rhubarb\Message;
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
        $this->connection->lpush($this->exchange, (string) $task);
    }
}
