<?php
namespace Rhubarb\Connector;

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
 * @package     
 * @category    
 * @subcategory 
 */
use \Rhubarb\Rhubarb;

/**
 * @package     
 * @category    
 * @subcategory 
 */
class AbstractConnector implements ConnectorInterface
{
    
    /**
     * @var array
     */
    protected $options = array();
    protected $rhubarb;


    /**
     * @param \Rhubarb\Rhubarb $rhubarb
     * @param array $options
     */
    public function __construct(Rhubarb $rhubarb, array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return self
     *
     * @throws \UnexpectedValueException
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
    /**
     *
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

}
 
