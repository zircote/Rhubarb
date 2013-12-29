<?php
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
 * @category    ${NAMESPACE}
 */

/* basic arg factory usage */
$args = \Rhubarb\Task\Args::newArgs(
    \Rhubarb\Task\Args\Python::LANG,
    2,
    2,
    \Rhubarb\Task\Args\Python\Kwargs::newKwargs(array('args1'=>'val1', 'arg2' => 'val2'))
);

/* Explicit Python args usage  with both star args and kwargs */
$args = new \Rhubarb\Task\Args\Python(
    2,
    2,
    \Rhubarb\Task\Args\Python\Kwargs::newKwargs(array('args1'=>'val1', 'arg2' => 'val2'))
);

/* Example of using Kwargs with both object and array access and Usage of the Python default lang type */
$kwargs = new \Rhubarb\Task\Args\Python\Kwargs();
$kwargs->arg1 = 'val1';
$kwargs['arg2'] = 'val2';
$args = \Rhubarb\Task\Args::newArgs(null, $kwargs);

/* example usage of custom arg types */
/**
 * Class PhpArgs
 * 
 * An hypothetical example class of an Args class for PHP
 */
class PhpArgs implements \Rhubarb\Task\Args\ArgsInterface
{
    const LANG = 'php';
    /**
     * @return mixed
     */
    public function serialize()
    {
        // TODO: Implement serialize() method.
    }

    /**
     * @return string
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    /**
     * @return array
     */
    public function toArray()
    {
        // TODO: Implement toArray() method.
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        // TODO: Implement getHeaders() method.
    }
}
/* Register the new Arg class with \Rhubarb\Task\Args */ 
\Rhubarb\Task\Args::registerType(\PhpArgs::LANG, '\PhpArgs');
$phpArgs = \Rhubarb\Task\Args::newArgs(PhpArgs::LANG);
