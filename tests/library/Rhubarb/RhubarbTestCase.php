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
 * @category    RhubarbTests\Mock
 */
use PHPUnit_Framework_TestCase;
use Rhubarb\Task\Message;
use Rhubarb\Task\Signature;

/**
 * @package     Rhubarb
 * @category    RhubarbTests\Mock
 */
class RhubarbTestCase extends PHPUnit_Framework_TestCase
{


    /**
     * @var \Rhubarb\Rhubarb
     */
    protected $rhubarb;
    /**
     * @var \Rhubarb\Task\Signature
     */
    protected $signature;
    protected $fixture;

    /**
     * @param $brokerMock
     * @param $resultStoreMock
     * @param Message $message
     * @return \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Rhubarb
     */
    public function getRhubarbMock($brokerMock, $resultStoreMock = null, Message $message = null)
    {
        $rhubarb = $this->getMock('\Rhubarb\Rhubarb', array('getBroker', 'dispatch', 'getResultStore'), array());
        $rhubarb->expects($this->any())
            ->method('getBroker')
            ->will($this->returnValue($brokerMock));

        if ($message instanceof Message) {
            $asyncMock = $this->getAsyncResultMock($rhubarb, $message);
            $rhubarb->expects($this->once())
                ->method('dispatch')
                ->will($this->returnValue($asyncMock));

        }

        if ($resultStoreMock) {
            $rhubarb->expects($this->any())
                ->method('getResultStore')
                ->will($this->returnValue($resultStoreMock));
        }

        return $rhubarb;
    }

    /**
     * @param Rhubarb $rhubarbMock
     * @param Message $message
     * @return \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Task\AsyncResult
     */
    public function getAsyncResultMock(Rhubarb $rhubarbMock, Message $message)
    {
        $asyncResultMock = $this->getMock('\Rhubarb\Task\AsyncResult', array(), func_get_args());

        return $asyncResultMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\ResultStore\ResultStoreInterface
     */
    protected function getResultStoreMock()
    {
        $brokerMock = $this->getMock('\Rhubarb\ResultStore\ResultStoreInterface');

        return $brokerMock;
    }

    /**
     * @param $brokerHeaders
     * @param $brokerProperties
     * @return \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Broker\BrokerInterface
     */
    protected function getBrokerMock($brokerHeaders = array(), $brokerProperties = array())
    {
        $brokerMock = $this->getMock(
            '\Rhubarb\Broker\BrokerInterface',
            array('getHeaders', 'getProperties', 'publishTask')
        );

        $brokerMock->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue($brokerHeaders));

        $brokerMock->expects($this->any())
            ->method('getProperties')
            ->will($this->returnValue($brokerProperties));

        $brokerMock->expects($this->any())
            ->method('publishTask')
            ->will($this->returnSelf());
        return $brokerMock;
    }

    /**
     * @param $rhubarb
     * @param $signatureHeaders
     * @param $signatureProperties
     * @param $body
     * @return \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Task\Signature
     */
    protected function getSignatureMock($rhubarb, $signatureHeaders, $signatureProperties, $body)
    {
        $signatureMock = $this->getMock(
            '\Rhubarb\Task\Signature',
            array('getProperties', 'getHeaders', 'getBody'),
            array($rhubarb, 'test.task')
        );

        $signatureMock->expects($this->any())
            ->method('getProperties')
            ->will($this->returnValue($signatureProperties));

        $signatureMock->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue($signatureHeaders));

        $bodyMock = $this->getBodyMock($body);

        $signatureMock->expects($this->any())
            ->method('getBody')
            ->will($this->returnValue($bodyMock));

        return $signatureMock;
    }

    /**
     * @param $body
     * @return \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Task\Body\BodyInterface
     */
    protected function getBodyMock($body)
    {
        $bodyMock = $this->getMock(
            '\Rhubarb\Task\Body\BodyInterface',
            array(),
            array('toArray', 'serialize', '__toString', 'getHeaders')
        );
        $bodyMock->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue($body));

        $bodyMock->expects($this->any())
            ->method('serialize')
            ->will($this->returnValue($body));

        $bodyMock->expects($this->any())
            ->method('getHeaders')
            ->will($this->returnValue(array('lang' => 'py')));

        $bodyMock->expects($this->any())
            ->method('__toString')
            ->will(
                $this->returnValue(
                    json_encode($body, JSON_BIGINT_AS_STRING | JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES)
                )
            );
        return $bodyMock;
    }

    /**
     * @param array $args
     * @param array $kwargs
     * @return \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Task\Body\Python
     */
    public function getPythonMock($args = array(), $kwargs = array())
    {
        $pythonMock = $this->getMock('Rhubarb\Task\Body\Python', array('toArray'), array());
        $pythonMock->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue(array('args' => $args, 'kwargs' => $this->getKwargsMock($kwargs))));

        return $pythonMock;
    }

    /**
     * @param array $kwargs
     * @return \PHPUnit_Framework_MockObject_MockObject|\Rhubarb\Task\Body\Python\Kwargs
     */
    public function getKwargsMock($kwargs = array())
    {
        $kwargsMock = $this->getMock('Rhubarb\Task\Body\Python\Kwargs', array('toArray'), array());
        $kwargsMock->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue($kwargs));

        return $kwargsMock;
    }

    /**
     * @param Rhubarb $rhubarb
     * @param Signature $signature
     * @return \PHPUnit_Framework_MockObject_MockObject|Message
     */
    public function getMessageMock(Rhubarb $rhubarb, Signature $signature)
    {
        $messageMock = $this->getMock('\Rhubarb\Task\Message', array(), func_get_args());
        return $messageMock;
    }

    public function testGetRhubarb()
    {

    }

    public function testSetRhubarb()
    {
        if ($this->fixture && method_exists($this->fixture, 'getRhubarb')) {
            $actual = $this->fixture->getRhubarb();
            $this->assertInstanceOf('\Rhubarb\Rhubarb', $actual);
        }
    }
}
 
