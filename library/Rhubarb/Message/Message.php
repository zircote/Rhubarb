<?php
namespace Rhubarb\Message;

/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2013] [Robert Allen]
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
 * @category    Rhubarb\Message
 */
use Rhubarb\Broker\BrokerInterface;
use Rhubarb\Exception\Exception;
use Rhubarb\Rhubarb;
use Rhubarb\Task\Body\BodyInterface;
use Rhubarb\Task\Signature;

/**
 * @package     Rhubarb
 * @category    Rhubarb\Message
 *
 */
class Message implements MessageInterface
{
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
            $brokerHeaders = array_filter($this->getRhubarb()->getBroker()->getHeaders(), array($this, 'filter'));
        }
        if ($this->getSignature() instanceof Signature) {
            $signatureHeaders = array_filter((array)$this->getSignature()->getHeaders(), array($this, 'filter'));
        }
        $this->headers = array_filter(
            array_merge($brokerHeaders, $this->headers, $signatureHeaders), array($this, 'filter')
        );
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
            $brokerProperties = array_filter(
                (array)$this->getRhubarb()->getBroker()->getProperties(), array($this, 'filter')
            );
        }
        if ($this->getSignature() instanceof Signature) {
            $signatureProperties = array_filter((array)$this->getSignature()->getProperties(), array($this, 'filter'));
        }
        $this->properties = array_filter(
            array_merge($brokerProperties, $this->properties, $signatureProperties), array($this, 'filter')
        );
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
            $this->payload = array(
                'headers' => $this->getHeaders(),
                'properties' => $this->getProperties(),
                'body' => $body
            );
        }
        return $this->payload;
    }

    /**
     * @return array|string
     */
    public function getBody()
    {
        if ($this->getSignature() instanceof Signature && $this->getSignature()
                ->getBody() instanceof BodyInterface
        ) {
            $body = $this->getSignature()
                ->getBody()
                ->toArray();
            return array_filter((array)$body, array($this, 'filter'));
        }
        return array();
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
            throw new Exception(
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
