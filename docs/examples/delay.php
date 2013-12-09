<?php
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
 * @category    Examples
 * 
 */
use Rhubarb\Rhubarb;
use Rhubarb\Task\Body\Python as PythonTask;
use Rhubarb\Task\Body\Python\Kwargs;
use Rhubarb\Task\Signature;
require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
$config = include('redis_configuration.php');
$rhubarb = new Rhubarb($config);

$kwargs = new Kwargs(array('arg1' => 1, 'arg2' => 2));
$argsPython = new PythonTask(array(1, 2), $kwargs);

/* @var Signature $add */
$add = $rhubarb->task('app.add');

$asyncResult = $add->delay($argsPython, array(), array('timelimit' => array(5.0, 10.0)));

//$result = $asyncResult->get();
