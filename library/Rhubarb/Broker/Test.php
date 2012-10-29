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
class Test implements BrokerInterface
{

    protected $nextResult;
    protected $exception;
    protected $published;

    protected $wait = 0;
    protected $timer;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {

    }

    public function setWait($wait = 0)
    {
        $this->wait = $wait;
    }
    /**
     * @param $result
     */
    public function setNextResult($result)
    {
        $this->nextResult = $result;
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
        $this->nextResult = null;
        $this->exception = null;
        $this->published = null;
    }

    /**
     * @param \Rhubarb\Task $task
     */
    public function publishTask(\Rhubarb\Task $task)
    {
        $this->published = (string) $task;
    }

    public function getPublishedValues()
    {
        return $this->published;
    }

    /**
     * @param \Rhubarb\Task $task
     *
     * @return bool|string
     */
    public function getTaskResult(\Rhubarb\Task $task)
    {
        if(!$this->timer){
            $this->timer = time() + $this->wait;
        }
        if($this->timer <= time()){
            return json_decode($this->nextResult);
        }
        return false;
    }
}
