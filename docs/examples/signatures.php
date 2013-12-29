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


use Rhubarb\Task\Args\Python;
use Rhubarb\Task\Args;
use Rhubarb\Task\Args\Python\Kwargs;
$rhubarb = new \Rhubarb\Rhubarb;

/* Signatures */
$sig = $rhubarb->task('tasks.add', Args::newArgs(Python::LANG, 2, 2), array('countdown' => 10));
// tasks.add(2, 2)

/* `Rhubarb::t` is a shortcut to `Rhubarb::task` */
$add = $rhubarb->t('task.add');
$add->s(Args::newArgs(Python::LANG, 2, 2));
// tasks.add(2, 2)


/* Access signature details */
$add = $rhubarb->task('task.add', Args::newArgs(Python::LANG, 2, 2, Kwargs::newKwargs(array('arg1' => 1, 'arg2' => 2))));
// tasks.add(2, 2, arg1=1, arg2=2)
$add->getArgs();
// array(
//     'args' => array(1, 2),
//     'kwargs' => array('arg1' => 1, 'arg2' => 2)
// );
$add->getHeaders();
// array(
//     'lang' => 'py',
//     'c_type' => 'tasks.add'
// );

/* Task Execution */
$add = $rhubarb->t('task.add');
$result = $add->applyAsync(Args::newArgs(Python::LANG, 2, 2));
$result->get();
// 4

$add = $rhubarb->t('task.add');
$result = $add->delay(Args::newArgs(Python::LANG, 2, 2), array(), array('countdown' => 1));
$result->get();
// 4
