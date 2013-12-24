<?php
namespace Rhubarb\Connector;

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
 * @package
 * @category
 * @subcategory
 */
use Rhubarb\Exception\ConnectionException;
use \Rhubarb\Rhubarb;

/**
 * @package
 * @category
 * @subcategory
 */
class AbstractConnector implements ConnectorInterface
{

    const AMQP_PERSISTENT = 2;
    const AMQP_NON_PERSISTENT = 1;
    /**
     * @var array
     */
    protected $options = array();
    /**
     * @var Rhubarb
     */
    protected $rhubarb;
    /**
     * @var array
     */
    protected $headers = array();
    /**
     * @var array
     */
    protected $properties = array(
        'content_type' => Rhubarb::CONTENT_TYPE_JSON,
        'content_encoding' => Rhubarb::CONTENT_ENCODING_UTF8,
        'delivery_mode' => self::AMQP_PERSISTENT,
        'priority' => 0
    );


    /**
     * @param \Rhubarb\Rhubarb $rhubarb
     * @param array $options
     */
    public function __construct(Rhubarb $rhubarb, array $options = array())
    {
        $this->setOptions($options);
        $this->setRhubarb($rhubarb);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param mixed $rhubarb
     * @return AbstractConnector
     */
    public function setRhubarb(Rhubarb $rhubarb)
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

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }
}
 
