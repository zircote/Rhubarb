<?php
namespace Rhubarb;

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
 * @category    Rhubarb
 */
use Rhubarb\Task\ResultBody;
use Rhubarb\Task\AsyncResult;

/**
 * @package     Rhubarb
 * @category    Rhubarb
 * @group Rhubarb
 * @group Rhubarb\Functional
 */
class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    protected $options = array(
        'broker' => array(
            'type' => 'Test',
            'class_namespace' => Rhubarb::BROKER_NAMESPACE
        ),
        'result_store' => array(
            'type' => 'Test',
            'class_namespace' => Rhubarb::RESULTSTORE_NAMESPACE
        ),
        'tasks' => array(
            array(
                'name' => 'app.add', // c_type
            ),
        ),
        'logger' => array(
            'loggers' => array(
                'dev' => array(
                    'level' => 'DEBUG',
                    'appenders' => array(__NAMESPACE__),
                ),
            ),
            'appenders' => array(
                __NAMESPACE__ => array(
                    'class' => 'LoggerAppenderNull'
                )
            )
        )
    );

    public function testRhubarbFunctional()
    {
        $rhubarb = new Rhubarb($this->options);
        $expected = 6;
        $rhubarb->getBroker()->setTaskCallback(
            function () {
                return new ResultBody(array('state' => AsyncResult::SUCCESS, 'result' => 6));
            }
        );
        $sig = $rhubarb->task('app.add');
        $res = $sig->delay();
        $this->assertEquals($expected, $res->get());
    }

    /**
     * @expectedException \Rhubarb\Exception\TimeoutException
     */
    public function testRhubarbFunctionalTimeoutExceeded()
    {
        $rhubarb = new Rhubarb($this->options);
        $rhubarb->getBroker()->setTaskCallback(
            function () {
                return new ResultBody(array('state' => AsyncResult::SUCCESS, 'result' => 6));
            }
        );
        $rhubarb->getResultStore()->setWait(3);
        $sig = $rhubarb->task('app.add');
        $res = $sig->delay();
        $res->get(2);
    }

    /**
     *
     */
    public function testRhubarbFunctionalTimeout()
    {
        $rhubarb = new Rhubarb($this->options);
        $expected = 6;
        $rhubarb->getBroker()->setTaskCallback(
            function () {
                return new ResultBody(array('state' => AsyncResult::SUCCESS, 'result' => 6));
            }
        );
        $rhubarb->getResultStore()->setWait(2);

        $sig = $rhubarb->task('app.add');
        $res = $sig->delay();
        $this->assertEquals($expected, $res->get(3));
    }
}
 
