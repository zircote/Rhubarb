<?php
namespace RhubarbTests;

use Rhubarb\Connector\Predis as PredisConnector;

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
 * @group Redis
 */
class PredisTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @group job
     */
    public function testJobSubmit()
    {
        if (!defined('CONNECTOR') || CONNECTOR != 'redis') {
            $this->markTestSkipped('skipped requires `redis` celery workers');
        }
        $options = array(
            'broker' => array(
                'type' => 'Predis',
                'options' => array(
                    'exchange' => 'celery',
                )
            ),
            'result_store' => array(
                'type' => 'Predis',
                'options' => array(
                    'exchange' => 'celery',
                )
            )
        );
        $rhubarb = new \Rhubarb\Rhubarb($options);

        $res = $rhubarb->sendTask('predis.add', array(2, 3));
        $res->delay();
//        $result = $res->get(2);
//        $this->assertEquals(5, $result);
        $res = $rhubarb->sendTask('predis.add', array(2102, 3));
        $res->delay();
//        $this->assertEquals(2105, $res->get());
    }

    public function testSetOptionsWhenNonZeroRedisDatabaseNumberIsSpecified()
    {
        $connector = new PredisConnector();
        $connector->setOptions(
            array('connection' => 'redis://127.0.0.1:6379/3')
        );

        $expectedOptions = array(
            'connection' => array(
                'database'=> 3,
                'host' => '127.0.0.1',
                'port' => 6379,
                'login' => null,
                'password' => null
            ),
            'options' => array()
        );

        $this->assertEquals($expectedOptions, $connector->getOptions());
    }
}
