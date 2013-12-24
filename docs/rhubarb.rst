Rhubarb
=======

.. topic:: Celery Worker Execution From PHP

    Use of Rhubarb is outlined as follows.

**Send AsyncResult and Wait For Result**
 
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
 
**Fire And Forget AsyncResult**
 
.. code-block:: php
 
     use \Rhubarb\Exception\TimeoutException;
     
     $rhubarb = new \Rhubarb\Rhubarb($options);
     
     try {
        $task = $rhubarb->sendTask('task.add', array(2,2));
        
        $result = $task->delay();
     } catch (TimeoutException $e) {
        $log->error('task failed to return in default timelimit [10] seconds');
     }
 
**Getting AsyncResult Status**
 
.. code-block:: php
 
     use \Rhubarb\Exception\TimeoutException;
     
     $rhubarb = new \Rhubarb\Rhubarb($options);
     
     $task = $rhubarb->sendTask('task.add', array(2,2));
        
     $result = $task->delay();
     
     while (!$task->isSuccess()) {
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
        $task->getPayload()
            ->setPropQueue('priority.high')
            ->setPropExchange('queue.other');
        $result = $task->delay();
        var_dump($task->get());
     } catch (TimeoutException $e) {
        $log->error('task failed to return in default timelimit [10] seconds');
     }
 
**Advanced AsyncResult Options**
 At runtime it may become necessary to utilize a different queue, exchange or various runtime options. These options may
 be passed to the __delay__ method when called:
 
 Supported Options are:
 
 
 - countdown: (int) The task is guaranteed to be executed at some time after the specified date and time, but not necessarily at that exact time.
 - expires: (int) The expires argument defines an optional expiry time, either as seconds after task publish.
 - priority: (int) A number between 0 and 9, where 0 is the highest priority. (Supported by: redis)
 - utc: (bool) Timestamps are UTC.
 - eta: (int) The ETA (estimated time of arrival) in seconds; lets you set a specific date and time that is the earliest time at which your task will be executed.
 - errbacks: TBD
 - queue: (string) Simple routing (name <-> name) is accomplished using the queue option.
 - queue_args: (array) Key-Value option pairs for the queue arguments.
 - exchange: (string) Name of exchange (or a kombu.entity.Exchange) to send the payload to.
 
 **Example**
 
 .. code-block:: php
    
    $rhubarb = new \Rhubarb\Rhubarb($options);
    
    $res = $rhubarb->sendTask('subtract', array(3, 2));
    $res->delay(
        array(
            'queue' => 'priority.high',
            'exchange' => 'subtract_queue'
        )
    );
    $result = $res->get(2);
    $this->assertEquals(1, $result);
