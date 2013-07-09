Rhubarb
=======

.. topic:: Celery Worker Execution From PHP
    
    Use of Rhubarb is outlined as follows.

**Send Task and Wait For Result**
 
.. code-block:: php
 
     use \Rhubarb\Exception\TimeoutException;
     
     $rhubarb = new \Rhubarb\Rhubarb($options);
     
     try {
        $task = $rhubarb->sendTask('task.add', array(2,2));
        $task->delay();
        $result = $task->get();
     } catch (TimeoutException $e) {
        $log->error('task failed to return in default timelimit [10] seconds');
     }
 
**Fire And Forget Task**
 
.. code-block:: php
 
     use \Rhubarb\Exception\TimeoutException;
     
     $rhubarb = new \Rhubarb\Rhubarb($options);
     
     try {
        $task = $rhubarb->sendTask('task.add', array(2,2));
        
        $result = $task->delay();
     } catch (TimeoutException $e) {
        $log->error('task failed to return in default timelimit [10] seconds');
     }
 
**Getting Task Status**
 
.. code-block:: php
 
     use \Rhubarb\Exception\TimeoutException;
     
     $rhubarb = new \Rhubarb\Rhubarb($options);
     
     $task = $rhubarb->sendTask('task.add', array(2,2));
        
     $result = $task->delay();
     
     while (!$task->successful()) {
        echo $task->state(), PHP_EOL;
        // You should have some time based break; statement here
     }
     
     var_dump($task->get());
 
**KWARG Support**
 
.. code-block:: php
 
     use \Rhubarb\Exception\TimeoutException;
     
     $rhubarb = new \Rhubarb\Rhubarb($options);
     try {
        $task = $rhubarb->sendTask('task.add', array('arg1' => 2, 'arg2' => 2));
        $result = $task->delay();
        var_dump($task->get());
     } catch (TimeoutException $e) {
        $log->error('task failed to return in default timelimit [10] seconds');
     }
 
**Method Specific Queue and/or Exchange**
 
.. code-block:: php
 
     use \Rhubarb\Exception\TimeoutException;
     
     $rhubarb = new \Rhubarb\Rhubarb($options);
     try {
        $task = $rhubarb->sendTask('task.add', array('arg1' => 2, 'arg2' => 2));
        $task->getMessage()
            ->setPropQueue('priority.high')
            ->setPropExchange('queue.other');
        $result = $task->delay();
        var_dump($task->get());
     } catch (TimeoutException $e) {
        $log->error('task failed to return in default timelimit [10] seconds');
     }
 
