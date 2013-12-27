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
use Rhubarb\Task\Body\Python as PythonArgs;

$config = include('configuration/predis.php');
$rhubarb = new Rhubarb($config);
$args = new PythonArgs(array(1, 2));

$rhubarb->task('app.add')
    ->delay($args, array(), array('countdown' => 60)); /* Task will execute in 60 seconds */
