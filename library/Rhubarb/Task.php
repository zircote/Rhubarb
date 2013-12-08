<?php
namespace Rhubarb;

/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2012] [Robert Allen]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package     Rhubarb
 * @category    Rhubarb
 * @subcategory Task
 */
use Rhumsaa\Uuid\Uuid;

/**
 * @package     Rhubarb
 * @category    Rhubarb
 * @subcategory Task
 */
class Task
{
    const PENDING = 'PENDING';
    const STARTED = 'STARTED';
    const SUCCESS = 'SUCCESS';
    const FAILURE = 'FAILURE';
    const RETRY   = 'RETRY';
    const REVOKED = 'REVOKED';

    /**
     * @var string
     */
    public $responseBody;
    /**
     * @var bool
     */
    protected $taskSent = false;
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $args = array();
    /**
     * @var Rhubarb/Rhubarb
     */
    protected $rhubarb;

    /**
     * @var \DateTime
     */
    protected $expires;
    /**
     * @var bool
     */
    protected $utc = true;
    /**
     * @var string
     */
    protected $callbacks;
    /**
     * @var string
     */
    protected $errbacks;
    /**
     * @var \DateTime
     */
    protected $eta;
    /**
     * @var int
     */
    protected $countdown;
    /**
     * @var int
     */
    protected $priority = 5;

    /**
     * id:    The unique id of the executing task.
     * args:    Positional arguments.
     * kwargs:    Keyword arguments.
     *
     * @var array
     */
    protected $kwargs = array();

    /**
     * @var Message
     */
    public $message;
    
    /**
     * @param string      $name
     * @param array       $args
     * @param Rhubarb     $rhubarb
     * @param null|string $id
     */
    public function __construct($name, $args = array(), Rhubarb $rhubarb, $id = null)
    {
        if(!$id){
           $id = (string) Uuid::uuid1();
        }
        $rhubarbOptions = $rhubarb->getOptions();
        $this->setId($id)
            ->setArgs($args)
            ->setName($name)
            ->setRhubarb($rhubarb);
        $this->message = new Message();
        if (isset($rhubarbOptions['broker']['options']['queue']['name'])) {
            $this->message->setQueue($rhubarbOptions['broker']['options']['queue']['name']);
        }
        if (isset($rhubarbOptions['broker']['options']['queue']['arguments'])) {
            $this->message->setPropQueueArgs($rhubarbOptions['broker']['options']['queue']['arguments']);
        }
        if (isset($rhubarbOptions['broker']['options']['exchange'])) {
            $this->message->setPropExchange($rhubarbOptions['broker']['options']['exchange']);
        }
    }

    /**
     * <b> Options:</b>
     * - countdown: (int) The task is guaranteed to be executed at some time after the specified date and time, but not necessarily at that exact time.
     * - expires: (int) The expires argument defines an optional expiry time, either as seconds after task publish.
     * - priority: (int) A number between 0 and 9, where 0 is the highest priority. (Supported by: redis)
     * - utc: (bool) Timestamps are UTC.
     * - eta: (int) The ETA (estimated time of arrival) in seconds; lets you set a specific date and time that is the earliest time at which your task will be executed.
     * - errbacks: TBD
     * - queue: Simple routing (name <-> name) is accomplished using the queue option.
     * - queue_args
     * - exchange: Name of exchange (or a kombu.entity.Exchange) to send the message to.
     * 
     * @param array $options
     * @throws \RuntimeException
     * @return Task
     */
    public function delay($options = array())
    {
        if ($this->getTaskSent()) {
            throw new \RuntimeException('Task has been sent');
        }
        if (isset($options['countdown'])) {
            $this->setCountdown($options['countdown']);
        }
        if (isset($options['expires'])) {
            $this->setExpires($options['expires']);
        }
        if (isset($options['priority'])) {
            $this->setPriority($options['priority']);
            $this->getMessage()->setPropPriority($options['priority']);
        }
        if (isset($options['no_ack'])) {
            $this->getMessage()->setPropNoAck($options['no_ack']);
        }
        if (isset($options['delivery_tag'])) {
            $this->getMessage()->setPropDeliveryTag($options['delivery_tag']);
        }
        if (isset($options['delivery_mode'])) {
            $this->getMessage()->setPropDeliveryMode($options['delivery_mode']);
        }
        if (isset($options['utc'])) {
            $this->setUtc((bool)$options['utc']);
        }
        if (isset($options['eta'])) {
            $this->setEta($options['eta']);
        }
        if (isset($options['errbacks'])) {
            $this->setCallbacks($options['errbacks']);
        }
        if (isset($options['queue'])) {
            $this->message->setQueue($options['queue']);
        }
        if (isset($options['queue_args'])) {
            $this->message->setPropQueueArgs($options['queue_args']);
        }
        if (isset($options['routing_key'])) {
            $this->message->setPropRoutingKey($options['routing_key']);
        }
        if (isset($options['exchange'])) {
            $this->message->setPropExchange($options['exchange']);
        } else {
            $this->message->setPropExchange(Rhubarb::RHUBARB_DEFAULT_EXCHANGE_NAME);
        }
        $this->setTaskSent(true);
        $this->getRhubarb()->getBroker()->publishTask($this);
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception\Exception
     */
    public function getResult()
    {
        if ($this->getRhubarb()->getResultStore()) {
            if(!$this->responseBody){
                $this->responseBody = $this->getRhubarb()->getResultStore()->getTaskResult($this);
            }
        } else {
            throw new \Rhubarb\Exception\Exception('no ResultStore is defined');
        }
        return $this->responseBody;
    }

    /**
     * @param array $args
     *
     * @return Task
     */
    public function setArgs(array $args)
    {
        foreach ($args as $k => $v) {
            if (is_numeric($k)) {
                $this->args[$k] = $v;
            } else {
                $this->kwargs[$k] = $v;
            }
            ksort($this->args);
        }
        return $this;
    }

    /**
     * @param Rhubarb $rhubarb
     *
     * @return Task
     */
    protected function setRhubarb(Rhubarb $rhubarb)
    {
        $this->rhubarb = $rhubarb;
        return $this;
    }

    /**
     * @return Rhubarb
     */
    protected function getRhubarb()
    {
        return $this->rhubarb;
    }

    /**
     *
     * @param string $id
     *
     * @return Task
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param string $name
     *
     * @return Task
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param string $callbacks
     *
     * @return Task
     */
    public function setCallbacks($callbacks)
    {
        $this->callbacks = $callbacks;
        return $this;
    }

    /**
     * @return string
     */
    public function getCallbacks()
    {
        return $this->callbacks;
    }

    /**
     *
     * @param string $errbacks
     * @return Task
     */
    public function setErrbacks($errbacks)
    {
        $this->errbacks = $errbacks;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrbacks()
    {
        return $this->errbacks;
    }

    /**
     * @param \DateTime $eta  
     * @return Task
     */
    public function setEta(\DateTime $eta)
    {
        $this->eta = $eta;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEta()
    {
        return $this->eta;
    }

    /**
     *
     * @param \DateTime $expires
     * @return Task
     */
    public function setExpires(\DateTime $expires)
    {
        $this->expires = $expires;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     *
     * @param array $kwargs
     * @return Task
     */
    public function setKwargs($kwargs)
    {
        $this->kwargs = $kwargs;
        return $this;
    }

    /**
     * @return array
     */
    public function getKwargs()
    {
        return $this->kwargs;
    }

    /**
     *
     * @param boolean $utc
     * @return Task
     */
    public function setUtc($utc)
    {
        $this->utc = $utc;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getUtc()
    {
        return $this->utc;
    }

    /**
     * @return string
     */
    public function state()
    {
        if ($this->ready()) {
            return $this->responseBody->status;
        } else {
            return self::PENDING;
        }
    }

    /**
     * @return mixed
     */
    public function traceback()
    {
        return $this->responseBody->traceback;
    }

    /**
     * @return bool
     */
    public function ready()
    {
        $this->getResult();
        return (bool) $this->responseBody;
    }

    /**
     * @return bool
     */
    public function successful()
    {
        $this->getResult();
        return $this->ready() && $this->responseBody->status == self::SUCCESS;
    }

    /**
     * @return bool
     */
    public function failed()
    {
        $this->getResult();
        return $this->ready() && !$this->successful();
    }

    /**
     * @param int   $timeout
     * @param float $interval
     *
     * @return mixed
     * @throws Exception\TimeoutException
     */
    public function get($timeout = 10, $interval = 0.5)
    {
        $intervalUs = (int)($interval * 1000000);
        $iterationLimit = (int)($timeout / $interval);

        for ($i = 0; $i < $iterationLimit; $i++) {
            if ($this->ready()) {
                break;
            }
            usleep($intervalUs);
        }

        if (!$this->ready()) {
            throw new \Rhubarb\Exception\TimeoutException(
                sprintf(
                    'Task %s(%s) did not return after %s seconds',
                    $this->getId(),
                    (string)$this,
                    $timeout
                )
            );
        }
        return $this->responseBody->result;
    }

    /**
     *
     * @param int $countdown
     *
     * @return Task
     */
    public function setCountdown($countdown)
    {
        $this->countdown = $countdown;
        return $this;
    }

    /**
     * @return int
     */
    public function getCountdown()
    {
        return $this->countdown;
    }

    /**
     *
     * @param int $priority
     * @return Task
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     *
     * @param string $responseBody
     * @return Task
     */
    public function setResponseBody($responseBody)
    {
        $this->responseBody = $responseBody;
        return $this;
    }

    /**
     * @return string
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     *
     * @param boolean $taskSent
     * @return Task
     */
    public function setTaskSent($taskSent)
    {
        $this->taskSent = $taskSent;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getTaskSent()
    {
        return $this->taskSent;
    }
    
    /**
     * @return array
     */
    public function toArray()
    {
        $body = array(
            'id'        => $this->id,
            'task'      => $this->name,
            'args'      => $this->args,
            'kwargs'    => (object) $this->kwargs,
            'expires'   => ($this->expires instanceof \DateTime) ? $this->expires->format(\DateTime::ISO8601) : null,
            'utc'       => (bool) $this->utc,
            'callbacks' => $this->callbacks,
            'eta'       => ($this->eta instanceof \DateTime) ? $this->eta->format(\DateTime::ISO8601) : null,
            'errbacks'  => $this->errbacks
        );
        $encoding = ($this->getMessage()->getBodyEncoding() || $this->getMessage()->getContentEncoding()); 
        switch ($encoding) {
            case Rhubarb::CONTENT_ENCODING_BASE64:
                $body = base64_encode(json_encode($body, JSON_UNESCAPED_SLASHES));
                break;
        }
        $this->getMessage()->setBody($body);
        return $body;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $encodedJson = json_encode($this->toArray(), JSON_UNESCAPED_SLASHES);
        return (string)$encodedJson;
    }

    /**
     *
     * @param \Rhubarb\Message $message
     * @return Task
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return \Rhubarb\Message
     */
    public function getMessage()
    {
        return $this->message;
    }
}

