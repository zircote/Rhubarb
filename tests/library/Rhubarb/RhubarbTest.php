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

    public function testJobSubmit()
    {
        $this->markTestSkipped('Requires a celery Worker with the test app running tests will be expanded');
        $rhubarb = new \Rhubarb\Rhubarb(
            array('amqp'=> array('uri' => 'amqp://guest:guest@localhost:5672/celery'))
        );

        $res = $rhubarb->sendTask('reaper.tasks.testFunc2', array('val1' => 2, 'val2' => 3));
        $this->assertEquals(5, $res->get());
        $res = $rhubarb->sendTask('reaper.tasks.testFunc2', array('val1' => 2102, 'val2' => 3));
        $this->assertEquals(2105, $res->get());
    }

}
