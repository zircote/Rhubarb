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
use Rhubarb\Task\Body\Python as PythonTask;

$config = include('configuration/predis.php');
$rhubarb = new Rhubarb($config);
$argsPython = new PythonTask(1, 2);

try {
    $result = $rhubarb->task('app.add')
        ->delay($argsPython, array())
        ->get();
} catch (\Rhubarb\Exception\TimeoutException $e) {
    /*
     * If the task result is not received within '10' seconds (default) a
     * `\Rhubarb\Exception\TimeoutException` is thrown. 
     */
    echo $e->getMessage(), PHP_EOL;
}
