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

}
