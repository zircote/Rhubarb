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
class Test implements BrokerInterface
{
    protected $exception;
    protected $published;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {

    }

    public function setOptions(array $options)
    {
        
    }
    
    public function getOptions()
    {
        
    }
    /**
     * @param \Exception $exception
     */
    public function throwExceptionOnNextRequest(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     *
     */
    public function reset()
    {
        $this->exception = null;
        $this->published = null;
    }

    /**
     * @param \Rhubarb\Task $task
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        
        $taskArray = $task->toArray();
        $this->published = json_encode($taskArray['body']);
    }

    public function getPublishedValues()
    {
        return $this->published;
    }
}
