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
use Rhubarb\Task\Args\Python;
use Rhubarb\Task\Args\Celery;

/**
 * @package     Rhubarb
 * @category    Rhubarb\Task
 */
class Args 
{
    /**
     * @var array
     */
    static protected $registry = array(
        Python::LANG => '\\Rhubarb\\Task\\Args\\Python',
        Celery::LANG => '\\Rhubarb\\Task\\Args\\Celery'
    );

    /**
     * @param $type
     * @param $class
     * 
     * @todo validate the class?
     */
    static public function registerType($type, $class)
    {
        static::$registry[$type] = $class;
    }
    /**
     * @param string $type
     * @param mixed $args
     * @return \Rhubarb\Task\Args\ArgsInterface|\Rhubarb\Task\Args\Python|\Rhubarb\Task\Args\Celery
     * @throws \InvalidArgumentException
     */
    static public function newArgs($type = null, $args = null)
    {
        if (!$type) {
            $type = Python::LANG;
        }
        if (!in_array($type, static::$registry)) {
            throw new \InvalidArgumentException(sprintf('arg type [ %s ] is not register', $type));
        }
        $reflect = new \ReflectionClass(static::$registry[$type]);
        return $reflect->newInstanceArgs(array_slice($args, 1));
    }
}
 
