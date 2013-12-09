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
 * @category    ${NAMESPACE}
 */

return array(
    'broker' => array(
        'connection' => 'tcp://localhost:6379?database=0',
        'type' => 'predis'
    ),
    'result_store' => array(
        'connection' => 'tcp://localhost:6379?database=0',
        'type' => 'predis'
    ),
    'tasks' => array(
        array(
            'name' => 'app.add', // c_type
            'callbacks' => array(
                array('name' => 'app.sum')
            ),
            'errbacks' => array(
                array('name' => 'app.debug')
            ),
            'timelimit' => 30
        ),
        array(
            'name' => 'app.sum', // c_type
            'errbacks' => array(
                array('name' => 'app.debug')
            ),
            array(
                'name' => 'app.debug', // c_type
            ),
            'logger' => array(
                'loggers' => array(
                    'Rhubarb' => array(
                        'appenders' => array(
                            'rhubarb_file',
                            'rhubarb_console'
                        ), array(
                            'level' => 'DEBUG',
                            'appenders' => array('default'),
                        ),
                    )
                ),
                'appenders' => array(
                    'rhubarb_console' => array(
                        'class' => 'LoggerAppenderFile',
                        'layout' => array(
                            'class' => 'LoggerLayoutConsole'
                        ),
                    ),
                    'rhubarb_file' => array(
                        'class' => 'LoggerAppenderFile',
                        'layout' => array(
                            'class' => 'LoggerLayoutSimple'
                        ),
                        'params' => array(
                            'file' => '/var/log/rhubarb.log',
                            'append' => true
                        )
                    )
                )
            )
        )
    )
);
