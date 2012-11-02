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
     * @group job
     */
    public function testJobSubmit()
    {
        $this->markTestSkipped('skipped requires celery workers');
        $options = array(
            'broker' => array(
                'type' => 'Amqp',
                'options' => array(
                    'exchange' => 'celery',
                    'uri' => 'amqp://celery:celery@localhost:5672/celery'
                )
            ),
            'result_store' => array(
                'type' => 'Amqp',
                'options' => array(
                    'exchange' => 'celeryresults',
                    'uri' => 'amqp://celery:celery@localhost:5672/celery'
                )
            )
        );
        $rhubarb = new \Rhubarb\Rhubarb($options);

        $res = $rhubarb->sendTask('proj.tasks.add', array(2, 3));
        $res->delay();
        $result = $res->get(2);
        $this->assertEquals(5, $res->get());
        $res = $rhubarb->sendTask('proj.tasks.add', array(2102, 3));
        $res->delay();
        $this->assertEquals(2105, $res->get());
    }

    /**
     *
     */
    public function testRhubarb()
    {
        $this->resultStore->setNextResult(false);
        $task = $this->rhubarb->sendTask('test.task', array(2,2));
        $task->delay();
        $expectedArgs = sprintf('{"id":"%s","task":"test.task","args":[2,2],"kwargs":{},"expires":null,"utc":true,'.
                '"callbacks":null,"eta":null,"errbacks":null}', $task->getId());
        $expected = $this->getSuccesfulResult('SUCCESS', $task->getId());
        $this->resultStore->setNextResult($expected);
        $this->assertEquals($expectedArgs, $this->broker->getPublishedValues());
        $this->assertEquals(2105, $task->get());
        $this->assertEquals('SUCCESS', $task->state());
        $this->assertNull($task->traceback());
        $this->assertFalse($task->failed());
        $this->assertTrue($task->successful());
    }

    public function testKwargsArePassed()
    {

        $this->resultStore->setNextResult(false);
        $res = $this->rhubarb->sendTask('test.task', array('arg1' => 2, 'arg2' => 2));
        $expected = sprintf(
            '{"id":"%s","task":"test.task","args":[],"kwargs":{"arg1":2,"arg2":2},"expires":null,"utc":true,'.
                '"callbacks":null,"eta":null,"errbacks":null}',
            $res->getId()
        );
        $res->delay();
        $this->assertEquals($expected, $this->broker->getPublishedValues());
    }

    /**
     * @expectedException \Rhubarb\Exception\TimeoutException
     */
    public function testTimeout()
    {
        $this->resultStore->setWait(4);
        $res = $this->rhubarb->sendTask('test.task', array(2,2));
        $res->delay();
        $this->resultStore->setNextResult($this->getSuccesfulResult('SUCCESS', $res->getId()));
        $res->get(1);
    }

    /**
     *
     */
    public function testTimeWaits()
    {
        $this->resultStore->setWait(4);
        $task = $this->rhubarb->sendTask('test.task', array(2,2));
        $this->resultStore->setNextResult($this->getSuccesfulResult('SUCCESS', $task->getId()));
        $task->delay();
        $this->assertEquals(2105, $task->get());
    }
}
