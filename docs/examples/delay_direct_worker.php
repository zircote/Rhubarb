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

/* @var Signature $add */
$add = $rhubarb->task('app.add')->si($argsPython);

/*
 * Celery Direct Queue Routing for Workers:
 * 
 * Given the routing queue as: w1@example.com.dq
 * and Config Option: CELERY_WORKER_DIRECT = True
 * The following will route the request to a specific worker with a 60 second delay:
 * 
 */
$asyncResult = $add->delay(
    $argsPython,
    array('countdown' => 60),
    array('routing_key' => 'W1@127.0.0.1', 'exchange' => 'C.dq')
);

//$result = $asyncResult->get();
