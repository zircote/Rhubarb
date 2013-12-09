<?php
namespace Rhubarb\Task\Body;

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
 * @category    Rhubarb\Task\Body
 */
use Rhubarb\Rhubarb;
use Rhubarb\Task\Body\Python\Kwargs;

/**
 * @package     Rhubarb
 * @category    Rhubarb\Task\Body
 */
class Python implements BodyInterface
{
    const LANG = 'py';
    
    /**
     * @var array
     */
    protected $args = array();
    /**
     * @var array
     */
    protected $headers = array('lang' => self::LANG);

    /**
     * @var Kwargs
     */
    protected $kwargs;

    public function __construct($args = array(), Kwargs $kwargs = null)
    {
        if ($args) {
            $this->setArgs($args);
        }
        if ($kwargs instanceof Kwargs) {
            $this->setKwargs($kwargs);
        }
    }
    /**
     *
     * @param array $args
     * @return Python
     */
    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     *
     * @param \Rhubarb\Task\Body\Python\Kwargs $kwargs
     * @return Python
     */
    public function setKwargs($kwargs)
    {
        $this->kwargs = $kwargs;
        return $this;
    }

    /**
     * @return \Rhubarb\Task\Body\Python\Kwargs
     */
    public function getKwargs()
    {
        return $this->kwargs;
    }

    public function toArray()
    {
        return array(
            'args' => $this->args,
            'kwargs' => $this->kwargs->toArray()
        );
    }
    /**
     * @return string
     */
    public function serialize()
    {
        return json_encode($this->toArray(), Rhubarb::$jsonOptions);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->serialize();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
 
