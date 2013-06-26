<?php
namespace RhubarbTests;

/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2012] [Robert Allen]
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
 * @category    Tests
 * @subcategory Task
 */

/**
 * @package     Rhubarb
 * @category    Tests
 * @subcategory Rhubarb
 * @group Amqp
 */
class AmqpTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @group job
     */
    public function testJobSubmit()
    {
        if (!defined('RUN_AMQP_TESTS') || !RUN_AMQP_TESTS) {
            $this->markTestSkipped('skipped requires celery workers');
        }
        $options = array(
            'broker' => array(
                'type' => 'Amqp',
                'options' => array(
                    'exchange' => 'celery',
                    'queue' => array(
                        'arguments' => array(
                        )
                    ),
                    'uri' => 'amqp://guest:guest@localhost:5672//celery'
                )
            ),
            'result_store' => array(
                'type' => 'Amqp',
                'options' => array(
                    'exchange' => 'celery',
                    'uri' => 'amqp://guest:guest@localhost:5672//celery'
                )
            )
        );
        $rhubarb = new \Rhubarb\Rhubarb($options);

        $res = $rhubarb->sendTask('tasks.add', array(2, 3));
        $res->delay();
        $result = $res->get(2);
        $this->assertEquals(5, $result);
        $res = $rhubarb->sendTask('tasks.add', array(2102, 3));
        $res->delay();
        $this->assertEquals(2105, $res->get());
    }

}
