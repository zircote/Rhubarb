<?php
namespace Rhubarb\ResultStore;

/**
 * @package     Rhubarb
 * @category    ResultStore
 */
/**
 * @package     Rhubarb
 * @category    ResultStore
 */
interface ResultStoreInterface
{
    /**
     * @abstract
     *
     * @param \Rhubarb\Task $task
     *
     * @return string|bool
     */
    public function getTaskResult(\Rhubarb\Task $task);
}
