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
 * @package
 * @category
 * @subcategory
 */
use \Rhubarb\Exception\KwargException;

/**
 * @package
 * @category
 * @subcategory
 */
class Kwargs
{
    protected $kwargs = array();

    /**
     * @param $name
     * @return null
     */
    function __get($name)
    {
        if (isset($this->kwargs[$name])) {
            return $this->kwargs[$name];
        }
        return null;
    }

    /**
     * @param $name
     * @param $value
     * @throws \Rhubarb\Exception\KwargException
     */
    public function __set($name, $value)
    {
        if (!preg_match('#^([_a-zA-Z]{1})([a-zA-Z0-9_]+)$#', $name)) {
            throw new KwargException(
                'kwarg name is not valid required format: [^([_a-zA-Z]{1})([a-zA-Z0-9_]+)$]'
            );
        }
        $this->kwargs[$name] = $value;
    }

    /**
     * @param $name
     */
    function __unset($name)
    {
        if (isset($this->kwargs[$name])) {
            unset($this->kwargs[$name]);
        }
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->kwargs;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
 
