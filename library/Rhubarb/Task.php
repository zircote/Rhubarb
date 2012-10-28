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

    public function setRhubarb(Rhubarb $rhubarb)
    {
        $this->rhubarb = $rhubarb;
        return $this;
    }

    /**
     *
     */
    public function publishMessage()
    {
        $this->getRhubarb()->getBroker()->publishTask($this);
    }

    /**
     * @return string|bool
     */
    public function getResult()
    {
        return $this->getRhubarb()->getResultBroker()->getTaskResult($this);
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
}
