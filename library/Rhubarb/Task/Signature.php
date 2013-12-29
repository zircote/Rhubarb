<?php
namespace Rhubarb\Task;

/**
 *
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
use Rhubarb\Exception\Exception;
use Rhubarb\Task\Message;
use Rhubarb\Rhubarb;
use Rhubarb\Exception\TaskSignatureException;
use Rhubarb\Task\Args\ArgsInterface;
use Rhumsaa\Uuid\Uuid;
use InvalidArgumentException;

/**
 * @package     Rhubarb
 * @category    Rhubarb\Task
 */
class Signature
{
    /**
     *
     */
    const MUTABLE = true;
    /**
     *
     */
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
     * @var ArgsInterface
     */
    protected $args = array();
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

    /**
     * @var array
     */
    protected $callbacks = array(
        'on_success' => array(),
        'on_failure' => array(),
        'on_retry' => array()
    );

    /**
     * @param Rhubarb $rhubarb
     * @param $name
     * @param ArgsInterface $body
     * @param array $headers
     * @param array $properties
     */
    public function __construct(Rhubarb $rhubarb, $name, ArgsInterface $body = null, $properties = array(), $headers = array())
    {
        $this->setHeader('c_type', $name);
        $this->setRhubarb($rhubarb);
        $this->setName($name);
        if ($body instanceof ArgsInterface) {
            $this->setArgs($body);
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
     * @param ArgsInterface $body
     * @return Signature
     */
    public function setArgs(ArgsInterface $body)
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        $this->args = $body;
        return $this;
    }

    /**
     * @return ArgsInterface
     */
    public function getArgs()
    {
        return $this->args;
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
        if ($this->getArgs()) {
            return array_merge($this->headers, $this->getArgs()->getHeaders());
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
        array_push($this->callbacks['on_retry'], $callback);
        return $this;
    }

    /**
     * @param ArgsInterface $body
     * @return $this
     */
    public function s(ArgsInterface $body = null)
    {
        if ($body) {
            $this->setArgs($body);
        }
        return $this;
    }

    /**
     * @param ArgsInterface $body
     * @return $this
     */
    public function si(ArgsInterface $body = null)
    {
        $this->isMutable(self::IMMUTABLE);
        if ($body) {
            $this->setArgs($body);
        }
        return $this;
    }

    /**
     * @param ArgsInterface $body
     * @param array $properties
     * @param array $headers
     * @return AsyncResult
     * @throws \Rhubarb\Exception\TaskSignatureException
     * @throws \InvalidArgumentException
     */
    public function applyAsync($body = null, array $properties = array(), array $headers = array())
    {
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        foreach ($properties as $property => $value) {
            $this->setProperty($property, $value);
        }
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }
        if ($body) {
            if ($body instanceof ArgsInterface) {
                $this->setArgs($body);
            } else {
                throw new InvalidArgumentException('$args argument must be of type `ArgsInterface`');
            }
        }
        $this->getId();
        $message = new Message($this->getRhubarb(), $this);
        return $message->dispatch($this);
    }

    /**
     * @param ArgsInterface $body
     * @param array $properties
     * @param array $headers
     * @return AsyncResult
     * @throws \Rhubarb\Exception\Exception
     */
    public function delay(ArgsInterface $body = null, array $properties = array(), array $headers = array())
    {
        return $this->applyAsync($body, $properties, $headers);
    }

    /**
     * @param ArgsInterface $args
     * @return $this
     * @codeCoverageIgnore
     */
    public function map(ArgsInterface $args)
    {
        $this->setArgs($args);
        return $this;
    }

    /**
     * @param ArgsInterface $args
     * @return $this
     * @codeCoverageIgnore
     */
    public function starmap(ArgsInterface $args)
    {
        $this->setArgs($args);
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
            throw new TaskSignatureException('Signature is Frozen');
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
        if ($this->isFroze()) {
            throw new TaskSignatureException('Signature is Frozen');
        }
        if (!$rhubarb instanceof Rhubarb) {
            if (is_object($rhubarb)) {
                $type = get_class($rhubarb);
            } else {
                $type = gettype($rhubarb);
            }
            throw new InvalidArgumentException(
                sprintf('argument must be of type [\Rhubarb\Rhubarb] [%s] given', $type)
            );
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
            $this->getArgs(),
            $this->getHeaders(),
            $this->getProperties()
        );
        return $clone;
    }

    /**
     * @param ArgsInterface $body
     * @param array $properties
     * @param array $headers
     * @return AsyncResult
     */
    public function __invoke($body = null, array $properties = array(), array $headers = array())
    {
        return call_user_func(array($this, 'applyAsync'), func_get_args());
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
