<?php
namespace Rhubarb\Result;

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
class AsynchResult
{
    const PENDING = 'PENDING';
    const STARTED = 'STARTED';
    const SUCCESS = 'SUCCESS';
    const FAILURE = 'FAILURE';
    const RETRY   = 'RETRY';
    const REVOKED = 'REVOKED';
    /**
     * @var \Rhubarb\Task
     */
    protected $task;
    /**
     * @var string
     */
    protected $body;
    /**
     * @var \AMQP\Channel
     */
    protected $channel;
    /**
     * @var \AMQP\Connection
     */
    protected $connection;

    /**
     * @param \Rhubarb\Task $task
     */
    public function __construct(\Rhubarb\Task $task)
    {
        $this->setTask($task);
        $this->getTask()->publishMessage();
    }

    public function state()
    {
        if ($this->ready()) {
            return $this->body->status;
        } else {
            return self::PENDING;
        }
    }

    public function traceback()
    {
        return $this->body->traceback;
    }

    public function getTaskId()
    {
        return $this->getTask()->getId();
    }

    /**
     * @return bool
     */
    public function ready()
    {
        $this->getResult();
        return $this->body;
    }

    public function successful()
    {
        $this->getResult();
        return $this->ready() && $this->body->state == self::SUCCESS;
    }

    public function failed()
    {
        $this->getResult();
        return $this->ready() && !$this->successful();
    }

    public function get($timeout = 10, $interval = 0.5)
    {
        $interval_us = (int)($interval * 1000000);
        $iteration_limit = (int)($timeout / $interval);

        $this->getResult();
        for ($i = 0; $i < $iteration_limit; $i++) {
            if ($this->ready()) {
                break;
            }
            usleep($interval_us);
        }

        if (!$this->ready()) {
            throw new \Rhubarb\Exception\TimeoutException(
                sprintf(
                    'AMQP task %s(%s) did not return after %s seconds',
                    $this->getTaskId(),
                    (string)$this->getTask(),
                    $timeout
                )
            );
        }
        return $this->body->result;
    }

    /**
     * @return bool
     */
    protected function getResult()
    {
        if (!$this->body) {
            $this->body = $this->getTask()->getResult();
        }
        return $this->body;
    }

    /**
     * @return \Rhubarb\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param \Rhubarb\Task $task
     *
     * @return AsynchResult
     */
    public function setTask(\Rhubarb\Task $task)
    {
        $this->task = $task;
        return $this;
    }
}

