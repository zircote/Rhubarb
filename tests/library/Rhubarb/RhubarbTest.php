<?php
namespace RhubarbTests;

/**
 * @package
 * @category
 * @subcategory
 */
/**
 * @package
 * @category
 * @subcategory
 */
class RhubarbTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Rhubarb\Broker\Test
     */
    protected $broker;
    /**
     * @var \Rhubarb\ResultStore\Test
     */
    protected $resultStore;
    /**
     * @var \Rhubarb\Rhubarb
     */
    protected $rhubarb;

    /**
     *
     */
    public function setup()
    {
        $options = array(
            'broker' => array(
                'type' => 'Test'
            ),
            'result_store' => array(
                'type' => 'Test'
            )
        );
        $this->rhubarb = new \Rhubarb\Rhubarb($options);
        /* @var \Rhubarb\Broker\Test $broker */
        $this->broker = $this->rhubarb->getBroker();
        $this->resultStore = $this->rhubarb->getResultStore();
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->rhubarb = null;
        $this->broker = null;
        $this->resultStore = null;
    }

    /**
     * @param string $status
     * @param        $taskId
     *
     * @return string
     */
    protected function getSuccesfulResult($status = 'SUCCESS', $taskId)
    {
        $resultExpected = sprintf(
            '{"status": "%s", "traceback": null, "result": 2105, "task_id": "%s", "children": []}',
            $status,
            $taskId
        );
        return $resultExpected;
    }

    /**
     *
     */
    public function testJobSubmit()
    {
        $this->markTestSkipped('skipped requires celery workers');
        $options = array(
            'broker' => array(
                'type' => 'Amqp',
                'options' => array(
                    'uri' => 'amqp://celery:celery@localhost:5672/celery'
                )
            )
        );
        $rhubarb = new \Rhubarb\Rhubarb($options);

        $res = $rhubarb->sendTask('reaper.tasks.testFunc2', array('val1' => 2, 'val2' => 3));
        $this->assertEquals(5, $res->get());
        $res = $rhubarb->sendTask('reaper.tasks.testFunc2', array('val1' => 2102, 'val2' => 3));
        $this->assertEquals(2105, $res->get());
    }

    /**
     *
     */
    public function testRhubarb()
    {
        $this->resultStore->setNextResult(false);
        $res = $this->rhubarb->sendTask('test.task', array(2,2));

        $expectedArgs = sprintf('{"id":"%s","task":"test.task","args":[2,2],"kwargs":{}}', $res->getTaskId());
        $this->resultStore->setNextResult($this->getSuccesfulResult('SUCCESS', $res->getTaskId()));

        $this->assertEquals($expectedArgs, $this->broker->getPublishedValues());
        $this->assertEquals(2105, $res->get());
        $this->assertEquals('SUCCESS', $res->state());
        $this->assertNull($res->traceback());
        $this->assertFalse($res->failed());
        $this->assertTrue($res->successful());
    }

    public function testKwargsArePassed()
    {

        $this->resultStore->setNextResult(false);
        $res = $this->rhubarb->sendTask('test.task', array('arg1' => 2, 'arg2' => 2));
        $expected = sprintf(
            '{"id":"%s","task":"test.task","args":[],"kwargs":{"arg1":2,"arg2":2}}',
            $res->getTaskId()
        );
        $this->assertEquals($expected, $this->broker->getPublishedValues());
    }

    /**
     * @expectedException \Rhubarb\Exception\TimeoutException
     */
    public function testTimeout()
    {
        $this->resultStore->setWait(4);
        $res = $this->rhubarb->sendTask('test.task', array(2,2));
        $this->resultStore->setNextResult($this->getSuccesfulResult('SUCCESS', $res->getTaskId()));
        $res->get(1);
    }

    /**
     *
     */
    public function testTimeWaits()
    {
        $this->resultStore->setWait(4);
        $res = $this->rhubarb->sendTask('test.task', array(2,2));
        $this->resultStore->setNextResult($this->getSuccesfulResult('SUCCESS', $res->getTaskId()));
        $this->assertEquals(2105, $res->get());
    }
}
