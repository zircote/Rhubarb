<?php
namespace Rhubarb\Task;

/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2012-2014], [Robert Allen]
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
 * @category    Rhubarb\Task
 */
use Rhubarb\Broker\BrokerInterface;
use Rhubarb\Exception\Exception;
use Rhubarb\Exception\MessageSentException;
use Rhubarb\Rhubarb;
use Rhubarb\Task\Body\BodyInterface;

/**
 * @package     Rhubarb
 * @category    Rhubarb\Task
 *
 */
class Message implements MessageInterface
{
    /**
     *
     */
    const V1 = 1;
    /**
     *
     */
    const V2 = 2;
    /**
     * @var int
     */
    static public $messageFormat = self::V2;
    /**
     * @var Rhubarb
     */
    protected $rhubarb;
    /**
     * @var Signature
     */
    protected $signature;
    /**
     * @var array
     */
    protected $payload = array();
    /**
     * @var array
     */
    protected $headers = array(
        'lang' => null,
        'c_type' => null,
        // Optional
        'c_meth' => null,
        'c_shadow' => null,
        'eta' => null,
        'expires' => null,
        'callbacks' => null,
        'errbacks' => null,
        'chain' => array(),
        'group' => array(), # group_id
        'chord' => array(), # chord
        'retries' => null,
        'timelimit' => array() # (time_limit, soft_time_limit)
    );
    /**
     * @var array
     */
    protected $properties = array(
        'correlation_id' => null,
        // Optional
        'reply_to' => null
    );
    /**
     * This is by definition laguage specific which implies multiple formats, this will likely become an object
     * to allow for multiple language formats that will intern be determined by such things as the `header['lang']`
     *
     * @var array
     */
    protected $body = array();
    /**
     * @var bool
     */
    private $isSent = false;

    /**
     * @param mixed $val
     * @return bool
     */
    private function filter($val)
    {
        if (is_array($val) || is_object($val)) {
            return (bool)$val;
        }
        return $val !== null && strlen($val) > 0;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->getProperty('correlation_id');
    }

    /**
     * @param Rhubarb $rhubarb
     * @param Signature $signature
     */
    public function __construct(Rhubarb $rhubarb, Signature $signature)
    {
        $this->setRhubarb($rhubarb);
        $this->setSignature($signature);
    }

    /**
     *
     * @param mixed $rhubarb
     * @return self
     */
    public function setRhubarb(Rhubarb $rhubarb)
    {
        $this->rhubarb = $rhubarb;
        return $this;
    }

    /**
     * @return Rhubarb
     */
    public function getRhubarb()
    {
        return $this->rhubarb;
    }

    /**
     * @param Signature $signature
     * @return $this
     */
    public function setSignature(Signature $signature)
    {
        $this->signature = $signature;
        return $this;
    }

    /**
     * @return \Rhubarb\Task\Signature
     */
    public function getSignature()
    {
        return $this->signature;
    }

    public function serialize()
    {
        return $this->getRhubarb()
            ->serialize($this->getPayload(), $this->getProperty('content_type') ? : Rhubarb::DEFAULT_CONTENT_TYPE);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->serialize();
    }

    /**
     * @return $this
     */
    protected function mergeHeaders()
    {
        $brokerHeaders = array();
        $signatureHeaders = array();
        if ($this->getRhubarb() && $this->getRhubarb()->getBroker() instanceof BrokerInterface) {
            $brokerHeaders = (array)$this->getRhubarb()->getBroker()->getHeaders();
        }
        if ($this->getSignature() instanceof Signature) {
            $signatureHeaders = (array)$this->getSignature()->getHeaders();
        }
        $this->headers = array_merge($brokerHeaders, $this->headers, $signatureHeaders);
        return $this;
    }

    /**
     * @return $this
     */
    protected function mergeProperties()
    {
        $brokerProperties = array();
        $signatureProperties = array();
        if ($this->getRhubarb() && $this->getRhubarb()->getBroker() instanceof BrokerInterface) {
            $brokerProperties = (array)$this->getRhubarb()->getBroker()->getProperties();
        }
        if ($this->getSignature() instanceof Signature) {
            $signatureProperties = (array)$this->getSignature()->getProperties();
        }
        $this->properties = array_merge($brokerProperties, $this->properties, $signatureProperties);
        return $this;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        $this->getSignature()->freeze();
        $this->mergeHeaders()->mergeProperties();
        if (!$this->payload) {
            if ($this->getProperty('content_encoding') !== Rhubarb::CONTENT_ENCODING_UTF8) {
                $body = $this->getRhubarb()->serialize($this->getBody());
            } else {
                $body = $this->getBody();
            }
            $body = $this->getRhubarb()->encode(
                $body,
                $this->getProperty('content_encoding') ? : Rhubarb::CONTENT_ENCODING_UTF8
            );
            $payload = array(
                'headers' => $this->getHeaders(),
                'properties' => $this->getProperties(),
                'body' => $body
            );

            if (isset($payload['headers']['countdown'])) {
                $datetime = new \DateTime();
                $datetime->add(new \DateInterval("PT{$payload['headers']['countdown']}S"));
                unset($payload['headers']['countdown']);
                $payload['headers']['eta'] = $datetime->format(\DateTime::ISO8601);
            }
            if (isset($payload['headers']['expires'])) {
                $datetime = new \DateTime();
                $datetime->setTimestamp(strtotime($payload['headers']['expires']));
                $payload['headers']['expires'] = $datetime->format(\DateTime::ISO8601);
            }
            if (static::$messageFormat == self::V2) {
                $this->payload = $this->getPayloadAsV2($payload);
            } else {
                $this->payload = $this->getPayloadAsV1($payload);
            }
        }

        return $this->payload;
    }

    /**
     * @param $payload
     * @return mixed
     */
    protected function getPayloadAsV1($payload)
    {
        return array(
            'properties' => array_filter((array)$payload['properties'], array($this, 'filter')),
            'headers' => array_diff_key($payload['headers'], $this->headers),
            'body' => array(
                'task' => $payload['headers']['name'],
                'id' => $payload['properties']['correlation_id'],
                'args' => $payload['body']['args'],
                'kwargs' => $payload['body']['kwargs'],
                'retries' => $payload['headers']['retries'],
                'eta' => $payload['headers']['eta'],
                'expire' => $payload['headers']['expires'],
                'utc' => true,
                'callbacks' => $payload['headers']['callbacks'],
                'errbacks' => $payload['headers']['errbacks'],
                'timelimit' => $payload['headers']['timelimit'],
                'taskset' => $payload['headers']['group'],
                'chord' => $payload['headers']['chord']
            ),
            'sent_event' => array(
                'uuid' => isset($payload['properties']['correlation_id']) ? 
                        $payload['properties']['correlation_id'] : null,
                'name' => isset($payload['headers']['c_type']) ? $payload['headers']['c_type'] : null,
                'args' => isset($payload['body']['args']) ? $payload['body']['args'] : null,
                'kwargs' => isset($payload['body']['kwargs']) ? $payload['body']['kwargs'] : null,
                'retries' => isset($payload['headers']['retries']) ? $payload['headers']['retries'] : null,
                'eta' => isset($payload['headers']['eta']) ? $payload['headers']['eta'] : null,
                'expires' => isset($payload['headers']['expires']) ? $payload['headers']['expires'] : null
            )
        );
    }

    /**
     * @param $payload
     * @return mixed
     */
    protected function getPayloadAsV2($payload)
    {
        return array(
            'headers' => array_filter((array)$payload['headers'], array($this, 'filter')),
            'properties' => array_filter((array)$payload['properties'], array($this, 'filter')),
            'body' => array_filter((array)$payload['body'], array($this, 'filter')),
            'sent_event' => array(
                'uuid' => isset($payload['properties']['correlation_id']) ? 
                        $payload['properties']['correlation_id'] : null,
                'name' => isset($payload['headers']['c_type']) ? $payload['headers']['c_type'] : null,
                'args' => isset($payload['body']['args']) ? $payload['body']['args'] : null,
                'kwargs' => isset($payload['body']['kwargs']) ? $payload['body']['kwargs'] : null,
                'retries' => isset($payload['headers']['retries']) ? $payload['headers']['retries'] : null,
                'eta' => isset($payload['headers']['eta']) ? $payload['headers']['eta'] : null,
                'expires' => isset($payload['headers']['expires']) ? $payload['headers']['expires'] : null
            )
        );
    }


    /**
     * @return array|string
     */
    public function getBody()
    {
        $body = array();
        if ($this->getSignature() instanceof Signature && $this->getSignature()
                ->getBody() instanceof BodyInterface
        ) {
            $body = $this->getSignature()
                ->getBody()
                ->toArray();
        }
        return $body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        $this->mergeHeaders();
        return $this->headers;
    }

    /**
     * @param string $header
     * @return string
     */
    public function getHeader($header)
    {
        $this->mergeHeaders();
        if (isset($this->headers[$header])) {
            return $this->headers[$header];
        }
    }

    /**
     * @param $header
     * @param $value
     * @returns $this
     * @throws Exception
     */
    public function setHeader($header, $value)
    {
        if ($this->isSent()) {
            throw new MessageSentException(
                sprintf(
                    'message sent, setting properties is not allowed [ %s(%s::%s) ]',
                    __METHOD__,
                    $header,
                    $value
                )
            );
        }
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        $this->mergeProperties();
        return $this->properties;
    }

    /**
     * @param string $property
     * @return string
     */
    public function getProperty($property)
    {
        $this->mergeProperties();
        if (isset($this->properties[$property])) {
            return $this->properties[$property];
        }
    }

    /**
     * @param $property
     * @param $value
     * @returns $this
     * @throws Exception
     */
    public function setProperty($property, $value)
    {
        if ($this->isSent()) {
            throw new MessageSentException(
                sprintf(
                    'message sent, setting properties is not allowed [ %s(%s::%s) ]',
                    __METHOD__,
                    $property,
                    $value
                )
            );
        }
        $this->properties[$property] = $value;
        return $this;
    }

    /**
     *
     * @return Message
     */
    public function setIsSent()
    {
        $this->isSent = true;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSent()
    {
        return $this->isSent;
    }

    /**
     * @return \Rhubarb\Task\AsyncResult
     */
    public function dispatch()
    {
        $asyncResult = $this->getRhubarb()->dispatch($this);
        $this->setIsSent();
        return $asyncResult;
    }

}
