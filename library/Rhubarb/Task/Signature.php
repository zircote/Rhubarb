<?php
namespace Rhubarb\Task;

/**
 *
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
use Rhubarb\Exception\Exception;
use Rhubarb\Message\Message;
use Rhubarb\Rhubarb;
use Rhubarb\Exception\TaskSignatureException;
use Rhubarb\Task\Body\BodyInterface;
use Rhumsaa\Uuid\Uuid;
use InvalidArgumentException;

/**
 * @package     Rhubarb
 * @category    Rhubarb\Message
 */
class Signature
{
    const MUTABLE = true;
    const IMMUTABLE = false;
    /**
     * @var string
     */
    protected $correlation_id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var array
     */
    protected $headers = array();
    /**
     * @var array
     */
    protected $properties = array(
        'timelimit' => array(null, null)
    );
    /**
     * @var BodyInterface
     */
    protected $body = array();
    /**
     * @var Rhubarb
     */
    protected $rhubarb;
    /**
     * @var bool
     */
    private $mutable = self::MUTABLE;
    /**
     * @var bool
     */
    private $frozen = false;

    protected $callbacks = array(
        'on_success' => array(),
        'on_error' => array(),
        'on_retry' => array()
    );

    /**
     * @param Rhubarb $rhubarb
     * @param $name
     * @param BodyInterface $body
     * @param array $headers
     * @param array $properties
     */
    public function __construct(Rhubarb $rhubarb, $name, BodyInterface $body = null, $properties = array(), $headers = array())
    {
        $this->setHeader('c_type', $name);
        $this->setRhubarb($rhubarb);
        $this->setName($name);
        if ($body instanceof BodyInterface) {
            $this->setBody($body);
        }
        if ($headers) {
            $this->setHeaders($headers);
        }
        if ($properties) {
            $this->setProperties($properties);
        }
    }

    /**
     * @throws TaskSignatureException
     * @param BodyInterface $body
     * @return Signature
     */
    public function setBody(BodyInterface $body)
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        $this->body = $body;
        return $this;
    }

    /**
     * @return BodyInterface
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @throws TaskSignatureException
     * @param $header
     * @param $value
     * @return $this
     */
    public function setHeader($header, $value)
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        $this->headers[$header] = $value;
        return $this;
    }

    /**
     * @throws TaskSignatureException
     * @param $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param $header
     * @return mixed
     */
    public function getHeader($header)
    {
        if (isset($this->headers[$header])) {
            return $this->headers[$header];
        }
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        if ($this->getBody()) {
            return array_merge($this->headers, $this->getBody()->getHeaders());
        }
        return $this->headers;
    }

    /**
     * @throws TaskSignatureException
     * @param $property
     * @param $value
     * @return $this
     */
    public function setProperty($property, $value)
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        $this->properties[$property] = $value;
        return $this;
    }

    /**
     * @throws TaskSignatureException
     * @param $properties
     * @return $this
     */
    public function setProperties($properties)
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        $this->properties = $properties;
        return $this;
    }

    /**
     * @param $property
     * @return mixed
     */
    public function getProperty($property)
    {
        if (isset($this->properties[$property])) {
            return $this->properties[$property];
        }
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param callable $callback
     * @return $this
     * @throws \Rhubarb\Exception\Exception
     * @throws TaskSignatureException
     */
    public function onSuccess(callable $callback)
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        if (!is_callable($callback)) {
            throw new TaskSignatureException('callback provided is not callable');
        }
        array_push($this->callbacks['on_success'], $callback);
        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     * @throws \Rhubarb\Exception\TaskSignatureException
     */
    public function onFailure(callable $callback)
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        if (!is_callable($callback)) {
            throw new TaskSignatureException('callback provided is not callable');
        }
        array_push($this->callbacks['on_failure'], $callback);
        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     * @throws TaskSignatureException
     */
    public function onRetry(callable $callback)
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        if (!is_callable($callback)) {
            throw new TaskSignatureException('callback provided is not callable');
        }
        array_push($this->callbacks['on_retry'], $callback);
        return $this;
    }

    /**
     * @param BodyInterface $body
     * @return $this
     */
    public function s(BodyInterface $body = null)
    {
        if ($body) {
            $this->setBody($body);
        }
        return $this;
    }

    /**
     * @param BodyInterface $body
     * @return $this
     */
    public function si(BodyInterface $body = null)
    {
        $this->isMutable(self::IMMUTABLE);
        if ($body) {
            $this->setBody($body);
        }
        return $this;
    }

    /**
     * @param BodyInterface $body
     * @param array $properties
     * @param array $headers
     * @return AsyncResult
     * @throws \Rhubarb\Exception\Exception
     */
    public function applyAsync(BodyInterface $body = null, array $properties = array(), array $headers = array())
    {
        if ($this->isFroze()) {
            throw new Exception('Signature is Frozen');
        }
        foreach ($properties as $property => $value) {
            $this->setProperty($property, $value);
        }
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }
        if ($body) {
            $this->setBody($body);
        }
        $this->getId();
        $message = new Message($this->getRhubarb(), $this);
        return $message->dispatch($this);
    }

    /**
     * @param BodyInterface $body
     * @param array $properties
     * @param array $headers
     * @return AsyncResult
     * @throws \Rhubarb\Exception\Exception
     */
    public function delay(BodyInterface $body = null, array $properties = array(), array $headers = array())
    {
        return $this->applyAsync($body, $properties, $headers);
    }

    /**
     * @param BodyInterface $args
     * @return $this
     */
    public function map(BodyInterface $args)
    {
        $this->setBody($args);
        return $this;
    }

    /**
     * @param BodyInterface $args
     * @return $this
     */
    public function starmap(BodyInterface $args)
    {
        $this->setBody($args);
        return $this;
    }

    /**
     * @throws \Rhubarb\Exception\Exception
     * @param string $name
     * @return Signature
     */
    public function setName($name)
    {
        if ($this->isFroze()) {
            throw new Exception('Signature is Frozen');
        }
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
     * @throws TaskSignatureException
     * @throws InvalidArgumentException
     * @param \Rhubarb\Rhubarb $rhubarb
     * @return Signature
     */
    public function setRhubarb($rhubarb)
    {
        if (!$rhubarb instanceof Rhubarb) {
            throw new InvalidArgumentException(
                sprintf('argument must be of type [\Rhubarb\Rhubarb] [%s] given', gettype($rhubarb))
            );
        }
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        $this->rhubarb = $rhubarb;
        return $this;
    }

    /**
     * @return \Rhubarb\Rhubarb
     */
    public function getRhubarb()
    {
        return $this->rhubarb;
    }

    /**
     * Freezing the signature should also render an UUID, rendering an UUID should freeze the Signature
     * @see self::getId()
     * @return $this
     */
    public function freeze()
    {
        if (!$this->getProperty('correlation_id')) {
            $this->getId();
        }
        $this->frozen = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFroze()
    {
        return $this->frozen;
    }

    /**
     * @param null $isMutable
     * @return bool
     */
    public function isMutable($isMutable = null)
    {
        if (null !== $isMutable && is_bool($isMutable)) {
            $this->mutable = $isMutable;
        }
        return $this->mutable;
    }

    /**
     * @return Signature
     */
    public function copy()
    {
        $clone = new Signature(
            $this->getRhubarb(),
            $this->getName(),
            $this->getBody(),
            $this->getHeaders(),
            $this->getProperties()
        );
        return $clone;
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this->applyAsync($this->getBody());
    }

    /**
     * @see self::freeze()
     * @return string
     */
    public function getId()
    {
        if (!$this->getProperty('correlation_id')) {
            $this->setProperty('correlation_id', (string)Uuid::uuid4());
        }
        $this->freeze();
        return $this->getProperty('correlation_id');
    }
}
