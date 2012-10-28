<?php
namespace Rhubarb\Broker;

/**
 * @package
 * @category
 * @subcategory
 */
/**
 * @package
 * @category
 * @subcategory
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

    /**
     * @abstract
     *
     * @param \Rhubarb\Task $task
     *
     * @return string|bool
     */
    public function getTaskResult(\Rhubarb\Task $task);
}
