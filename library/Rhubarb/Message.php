<?php
namespace Rhubarb;

/**
 * @package     Rhubarb
 * @category    Rhubarb
 * @subcategory Message
 */
use Rhubarb\Rhubarb;
use Rhumsaa\Uuid\Uuid;

/**
 * @package     Rhubarb
 * @category    Rhubarb
 * @subcategory Message
 * 
 * @property string $body
 * @property string $headers
 * @property string $contentType
 * @property bool   $propExclusive
 * @property string $propName
 * @property string $propBodyEncoding
 * @property int    $propPriority
 * @property string $propRoutingKey
 * @property string $propExchange
 * @property string $propDurable
 * @property string $propDeliveryMode
 * @property bool   $propNoAck
 * @property string $propAlias
 * @property array  $propQueueArgs
 * @property array  $propBindingArgs
 * @property string $propDeliveryTag
 * @property bool   $propAutoDelete
 * @property string $contentEncoding
 */
class Message 
{
    const BODY_ENCODING_BASE64 = 'base64';
    
    /**
     * @return array
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @var array
     */
    protected $message
        = array(
            'body'             => null,
            'headers'          => array(),
            'content-type'     => Rhubarb::RHUBARB_CONTENT_TYPE,
            'properties'       => array(
                'exclusive'         => null,
                'name'              => Rhubarb::RHUBARB_DEFAULT_TASK_QUEUE,
                'body_encoding'     => null,
                'delivery_info'     => array(
                    'priority'    => 0,
                    'routing_key' => null,
                    'exchange'    => Rhubarb::RHUBARB_DEFAULT_EXCHANGE_NAME
                ),
                'durable'           => true,
                'delivery_mode'     => 2,
                'no_ack'            => null,
                'alias'             => null,
                'queue_arguments'   => array(),
                'binding_arguments' => array(),
                'delivery_tag'      => null,
                'auto_delete'       => null,
            ),
            'content-encoding' => Rhubarb::RHUBARB_DEFAULT_CONTENT_ENCODING
        );

    /**
     * @var string
     */
    protected $queue = Rhubarb::RHUBARB_DEFAULT_TASK_QUEUE;

    public function __construct()
    {
    }
    /**
     *
     * @param string $queue
     * @return self
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }
    
    /**
     * @param $propRoutingKey
     * @return self
     */
    public function setPropRoutingKey($propRoutingKey)
    {
        $this->message['properties']['delivery_info']['routing_key'] = $propRoutingKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPropRoutingKey()
    {
        return  $this->message['properties']['delivery_info']['routing_key'];
    }

    /**
     * @param $propQueueArgs
     * @return self
     */
    public function setPropQueueArgs($propQueueArgs)
    {
        $this->message['properties']['queue_arguments'] = $propQueueArgs;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPropQueueArgs()
    {
        return$this->message['properties']['queue_arguments'];
    }

    /**
     * @param $propPriority
     * @return self
     */
    public function setPropPriority($propPriority)
    {
        $this->message['properties']['delivery_info']['priority'] = $propPriority;
        return $this;
    }

    /**
     * @return int
     */
    public function getPropPriority()
    {
        return $this->message['properties']['delivery_info']['priority'];
    }

    /**
     * @param bool $propNoAck
     * @return self
     */
    public function setPropNoAck($propNoAck)
    {
        $this->message['properties']['no_ack'] = $propNoAck;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPropNoAck()
    {
        return $this->message['properties']['no_ack'];
    }

    /**
     * @param bool $propName
     * @return self
     */
    public function setPropName($propName)
    {
        $this->message['properties']['name'] = $propName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropName()
    {
        return $this->message['properties']['name'];
    }

    /**
     * @param bool $propExclusive
     * @return self
     */
    public function setPropExclusive($propExclusive)
    {
        $this->message['properties']['exclusive'] = $propExclusive;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPropExclusive()
    {
        return $$this->message['properties']['exclusive'];
    }

    /**
     * @param string $propExchange
     * @return self
     */
    public function setPropExchange($propExchange)
    {
        $this->message['properties']['delivery_info']['exchange'] = $propExchange;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropExchange()
    {
        return $this->message['properties']['delivery_info']['exchange'];
    }

    /**
     * @param bool $propDurable
     * @return self
     */
    public function setPropDurable($propDurable)
    {
        $this->message['properties']['durable'] = $propDurable;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPropDurable()
    {
        return $this->message['properties']['durable'];
    }

    /**
     * @param string $propDeliveryTag
     * @return self
     */
    public function setPropDeliveryTag($propDeliveryTag)
    {
        $this->message['properties']['delivery_tag'] = $propDeliveryTag;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropDeliveryTag()
    {
        return $this->message['properties']['delivery_tag'];
    }

    /**
     * @param string $propDeliveryMode
     * @return self
     */
    public function setPropDeliveryMode($propDeliveryMode)
    {
        $this->message['properties']['delivery_mode'] = $propDeliveryMode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropDeliveryMode()
    {
        return $this->message['properties']['delivery_mode'];
    }

    /**
     * @param string $propBodyEncoding
     * @return self
     */
    public function setPropBodyEncoding($propBodyEncoding)
    {
        $this->message['properties']['body_encoding'] = $propBodyEncoding;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropBodyEncoding()
    {
        return $this->message['properties']['body_encoding'];
    }

    /**
     * @param array $propBindingArgs
     * @return self
     */
    public function setPropBindingArgs(array $propBindingArgs)
    {
        $this->message['properties']['binding_arguments'] = $propBindingArgs;
        return $this;
    }

    /**
     * @return array
     */
    public function getPropBindingArgs()
    {
        return $this->message['properties']['binding_arguments'];
    }

    /**
     * @param bool $propAutoDelete
     * @return self
     */
    public function setPropAutoDelete($propAutoDelete)
    {
        $this->propAutoDelete['properties']['auto_delete'] = $propAutoDelete;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPropAutoDelete()
    {
        return $this->message['properties']['auto_delete'];
    }

    /**
     * @param string $propAlias
     * @return self
     */
    public function setPropAlias($propAlias)
    {
        $this->message['properties']['alias'] = $propAlias;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropAlias()
    {
        return $this->message['properties']['alias'];
    }

    /**
     * @param array headers
     * @return self
     */
    public function setHeaders($headers)
    {
        $this->message['properties']['headers'] = $headers;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->message['properties']['headers'];
    }

    /**
     * @param string $contentType
     * @return self
     */
    public function setContentType($contentType)
    {
        $this->message['content-type'] = $contentType;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->message['content-type'];
    }

    /**
     * @param string $contentEncoding
     * @return self
     */
    public function setContentEncoding($contentEncoding)
    {
        $this->message['content-encoding'] = $contentEncoding;
        return $this;
    }

    /**
     * @return string
     */
    public function getContentEncoding()
    {
        return $this->message['content-encoding'];
    }

    /**
     * @param string $body
     * @return self
     */
    public function setBody($body)
    {
        $this->message['body'] = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->message['body'];
    }

    /**
     * @return array
     */
    public function toArray()
    {
       $message = $this->message;
        switch (strtolower($message['properties']['body_encoding'])) {
            case self::BODY_ENCODING_BASE64:
                $message['body'] = base64_encode(json_encode($message['body']));
                break;
        }
        return $message;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $message = $this->toArray();
        return json_encode($message);
    }
}
