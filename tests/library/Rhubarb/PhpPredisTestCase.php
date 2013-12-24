<?php
namespace Rhubarb;

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
     * @category    Rhubarb\Connector
     */

/**
 * @package     Rhubarb
 * @category    Rhubarb\Connector
 */
class PhpPredisTestCase extends RhubarbTestCase
{
    /**
     * @param bool $callOriginalConstructor
     * @param array $methods
     * @param array $constructorArguments
     * @return \PHPUnit_Framework_MockObject_MockObject|\Predis\Client
     */
    public function getPredisClientMock($callOriginalConstructor = true,
                                        $methods = array('lpush', 'publish', 'subscribe', 'unsubscribe', 'get'),
                                        $constructorArguments = array())
    {
        $predisMock = $this->getMock('\Predis\Client', $methods, $constructorArguments, '', $callOriginalConstructor);
        return $predisMock;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param string $method
     * @param \PHPUnit_Framework_MockObject_Stub $returns
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder|null $expects
     * @return \PHPUnit_Framework_MockObject_MockObject|\Predis\Client
     */
    public function setPredisMethod($mock, $method, $returns, $expects = null)
    {
        $mock->expects($expects ? : $this->any())
            ->method($method)
            ->will($returns);
        return $mock;
    }
}
 
