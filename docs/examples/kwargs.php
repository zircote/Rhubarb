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
 * @category    Examples
 *
 */
use Rhubarb\Rhubarb;
use Rhubarb\Task\Args\Python as PythonArgs;
use Rhubarb\Task\Args\Python\Kwargs;

$config = include('configuration/predis.php');
$rhubarb = new Rhubarb($config);

$kwargs = new Kwargs(array('arg3' => 'this is kwarg three'));
$kwargs['arg_1'] = 'my first arg';
$kwargs->arg2 = 'the second arg';

$args = new PythonArgs($kwargs);

$result = $rhubarb->task('app.add')
    ->delay($args)
    ->get();
