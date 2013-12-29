<?php
namespace Rhubarb\Task\Args;

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
 * @category    Rhubarb
 */
use Rhubarb\Task\Args\Celery\Arguments;
use Rhubarb\Rhubarb;

/**
 * @package     Rhubarb
 * @category    Rhubarb
 */
class Celery implements ArgsInterface
{
    const LANG = 'celery';
    /**
     * @var string
     */
    protected $destination;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var Arguments
     */
    protected $arguments;
    /**
     * @var array
     */
    protected $headers = array();

    /**
     * @param string $method
     * @param Arguments $args
     * @param null $destination
     */
    public function __construct($method, Arguments $args, $destination = null)
    {
        $this->method = $method;
        $this->arguments = $args;
        if (null !== $destination) {
            $this->destination = $destination;
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'destination' => $this->destination,
            'method' => $this->method,
            'arguments' => $this->arguments->toArray()
        );
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->serialize();
    }

    /**
     * @return string
     */
    public function serialize()
    {
        $args = $this->toArray();
        $str = json_encode($args, Rhubarb::$jsonOptions);
        return $str;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

}
 
