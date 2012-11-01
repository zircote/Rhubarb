<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
/**
 * @package     Rhubarb
 * @category    Broker
 */
interface BrokerInterface
{
    /**
     * @abstract
     *
     * @param \Rhubarb\Task $task
     *
     * @return void
     */
    public function publishTask(\Rhubarb\Task $task);

}
