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
 * @category    Rhubarb\Task\Args
 */
use Rhubarb\Rhubarb;
use Rhubarb\Task\Args\Python\Kwargs;

/**
 * @package     Rhubarb
 * @category    Rhubarb\Task\Args
 */
class Python implements ArgsInterface
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

    public function __construct()
    {
        foreach (func_get_args() as $args) {
            if ($args instanceof Kwargs) {
                $this->setKwargs($args);
            } else {
                array_push($this->args, $args);
            }
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
     * @param \Rhubarb\Task\Args\Python\Kwargs $kwargs
     * @return Python
     */
    public function setKwargs($kwargs)
    {
        $this->kwargs = $kwargs;
        return $this;
    }

    /**
     * @return \Rhubarb\Task\Args\Python\Kwargs
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
        return (string)$this->serialize();
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
 
