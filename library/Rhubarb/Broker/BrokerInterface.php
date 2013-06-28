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
     * @param \Rhubarb\Task $task
     *
     * @return void
     */
    public function publishTask(\Rhubarb\Task $task);

    /**
     * @return array
     */
    public function getOptions();

    /**
     * @param array $options
     *
     * @return self
     */
    public function setOptions(array $options);

}
