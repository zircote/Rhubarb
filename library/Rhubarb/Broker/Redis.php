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
class Redis implements BrokerInterface
{

    const KEYPREFIX_QUEUE = '_kombu.binding.%s';
    const SEPERATOR = "\x06\x16";
    public function publishTask(\Rhubarb\Task $task)
    {
        //'LPUSH';
    }
}
