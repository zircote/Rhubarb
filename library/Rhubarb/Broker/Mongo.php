<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
use Rhubarb\Message;
use Rhubarb\Rhubarb;
use Rhumsaa\Uuid\Uuid;
use Rhubarb\Connector\Mongo as MongoConnection;

/**
 * @package     Rhubarb
 * @category    Broker
 */
class Mongo extends MongoConnection implements BrokerInterface
{
    /**
     * @param \Rhubarb\Task $task
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        $task->getMessage()->setPropBodyEncoding(Message::BODY_ENCODING_BASE64);
        $collection = $this->getConnection()->selectCollection(self::CELERY_MESSAGES_COLLECTION);
        $collection->save(
            array('queue' => $task->getMessage()->getPropExchange(), 'payload' => (string) $task)
        );
    }
}
