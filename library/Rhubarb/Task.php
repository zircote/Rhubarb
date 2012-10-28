<?php
namespace Rhubarb;
/**
 * @package
 * @category
 * @subcategory
 */
use Rhubarb\Result\AsynchResult;

/**
 * @package
 * @category
 * @subcategory
 */
class Task
{
    const CELERY_SERIALIZER = 'json';
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
     * @var \AMQP\Channel
     */
    protected $channel;

    /**
     * id:    The unique id of the executing task.
     * args:    Positional arguments.
     * kwargs:    Keyword arguments.
     *
     * @var array
     */
    protected $kwargs = array();

    /**
     * @param string  $name
     * @param array   $args
     * @param Rhubarb $rhubarb
     */
    public function __construct($name, $args = array(), Rhubarb $rhubarb)
    {
        $parts = str_replace('-', null, implode(
            '_',
            array(Rhubarb::RHUBARB_USER_AGENT, RHubarb::RHUBARB_VERSION, gethostname(), getmypid())
        ));
        $this->setId(uniqid($parts, true))
            ->setArgs($args)
            ->setName($name)
            ->setRhubarb($rhubarb);

    }

    /**
     * @param Rhubarb $rhubarb
     *
     * @return Task
     */
    public function setRhubarb(\Rhubarb\Rhubarb $rhubarb)
    {
        $this->rhubarb = $rhubarb;
        return $this;
    }

    /**
     *
     */
    public function publishMessage()
    {
        $options = $this->rhubarb->getOptions();
        $channel = $this->rhubarb->getConnection()->channel();
        $channel->exchangeDeclare($options['celery']['results_exchange'], 'direct', false, false, false);
        $channel->exchangeDeclare($options['celery']['exchange'], 'direct', true, true);
        $channel->queueBind('celery', $options['celery']['exchange'], $this->getId());
        $channel->basicPublish(
            new \AMQP\Message((string) $this, array('content_type' => 'application/json')),
            $options['celery']['exchange'], $this->getId()
        );
        $channel->close();
    }
    /**
     *
     * @return AsynchResult
     */
    public function applyAsync()
    {
        return new AsynchResult($this);
    }

    /**
     * @param array $args
     *
     * @return Task
     */
    public function setArgs(array $args)
    {
        foreach ($args as $k => $v) {
            if (is_numeric($k)){
                $this->args[$k] = $v;
            } else {
                $this->kwargs[$k] = $v;
            }
            ksort($this->args);
        }
        return $this;
    }

    public function getRhubarb()
    {
        return $this->rhubarb;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'id'     => $this->id,
            'task'   => $this->name,
            'args'   => $this->args,
            'kwargs' => (object)$this->kwargs
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $encodedJson = json_encode($this->toArray());
        return (string)$encodedJson;
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
     * @return bool
     */
    public function getResult()
    {
        try {
            $channel = $this->getRhubarb()->getConnection()->channel();
            if($message = $channel->basicGet($this->getId())){
                $channel->basicAck($message->delivery_info['delivery_tag']);
                $body = json_decode($message->body);
                $channel->queueDelete($this->getId());
                $channel->close();
                return $body;
            }
            $channel->close();
            return false;
        } catch (\AMQP\Exception\ChannelException $e){
            return false;
        }
    }
}
