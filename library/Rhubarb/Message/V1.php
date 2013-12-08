<?php
namespace Rhubarb\Message;

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
 * @property string $contentEncoding
 * @property bool   $propExclusive
 * @property string $propName
 * @property string $bodyEncoding
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
 */
class V1
{
    /**
     * @var array
     */
    protected $message
        = array(
            'body'             => null,
            'headers'          => array(),
            'content-type'     => Rhubarb::RHUBARB_CONTENT_TYPE,
            'content-encoding' => Rhubarb::RHUBARB_DEFAULT_CONTENT_ENCODING,
            'properties'       => array(
                'correlation_id'    => null,
                'reply_to'          => null,
                'body_encoding'     => null,
                'exclusive'         => null,
                'name'              => Rhubarb::RHUBARB_DEFAULT_TASK_QUEUE,
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
                'auto_delete'       => false,
            )
        );

    /**
     * @var
     */
    protected $rhubarb;

    /**
     * @var string
     */
    protected $queue = Rhubarb::RHUBARB_DEFAULT_TASK_QUEUE;

    /**
     * @param Rhubarb $rhubarb
     */
    public function __construct(Rhubarb $rhubarb)
    {
        $this->setRhubarb($rhubarb);
    }

    /**
     * @param $reply_to
     */
    public function setReplyTo($reply_to)
    {
        $this->message['properties']['reply_to'] = $reply_to;
    }

    /**
     * @return mixed
     */
    public function getReplyTo()
    {
        return $this->message['properties']['reply_to'];
    }

    /**
     * @param $correlation_id
     */
    public function setCorrelationId($correlation_id)
    {
        $this->message['properties']['correlation_id'] = $correlation_id;
    }

    /**
     * @return mixed
     */
    public function getCorrelationId()
    {
        return $this->message['properties']['correlation_id'];
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
    public function setBodyEncoding($contentEncoding)
    {
        $this->message['properties']['body_encoding'] = $contentEncoding;
        return $this;
    }

    /**
     * @return string
     */
    public function getBodyEncoding()
    {
        return $this->message['properties']['body_encoding'];
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
        if (isset($message['properties'])) {
            $message['properties'] = array_filter($message['properties']);
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
    /**
     *
     * @param mixed $rhubarb
     * @return V1
     */
    public function setRhubarb($rhubarb)
    {
        $this->rhubarb = $rhubarb;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRhubarb()
    {
        return $this->rhubarb;
    }
}
