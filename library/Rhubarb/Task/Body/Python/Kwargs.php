<?php
namespace Rhubarb\Task\Body\Python;

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
use \Rhubarb\Exception\KwargException;
use ArrayAccess;

/**
 * @package
 * @category
 * @subcategory
 */
class Kwargs implements ArrayAccess
{
    protected $kwargs = array();

    /**
     * @param array $kwargs
     */
    public function __construct(array $kwargs = array())
    {
        foreach ($kwargs as $k => $v) {
            $this->offsetSet($k, $v);
        }
    }
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
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->kwargs);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->kwargs[$offset];
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->__set($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

}
 
