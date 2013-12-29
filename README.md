Rhubarb - A PHP Job Submission library for [Celery](http://celeryproject.org/)
==============================================================================
 - [![Master Build Status](https://secure.travis-ci.org/zircote/Rhubarb.png?branch=master)](http://travis-ci.org/zircote/Rhubarb) `master`
 - [![3.2-dev Development Build Status](https://api.travis-ci.org/zircote/Rhubarb.png?branch=3.2-dev)](http://travis-ci.org/zircote/Rhubarb) `3.2-dev`

# Requirements

 - This version of Rhubarb requires version >= 3.2 [Celery](https://github.com/celery/celery)
    - The Celery deployment details (tasks registered, configuration details and custom configuration elements)
 - A message broker, i.e. Redis or RabbitMQ and reliable connectivity to the Broker(s) of choice.
 - PHP version >= 5.4

# Get Started

Via Composer: `composer require 'zircote/rhubarb=dev-master'`

# Use:

## Simple Example

```php

use Rhubarb\Rhubarb;
use Rhubarb\Task\Args\Python as PythonArgs;

$config = array(
    'broker' => array(
        'connection' => 'tcp://localhost:6379?database=0',
        'type' => 'predis'
    ),
    'result_store' => array(
        'connection' => 'tcp://localhost:6379?database=0',
        'type' => 'predis'
    ),
    'tasks' => array(
        array(
            'name' => 'app.add', // c_type, this should match the registered name of the task as celery sees it.
            'timelimit' => 30
        )
    )
);
$rhubarb = new Rhubarb($config);

$argsPython = new PythonArgs(array(1, 2));

try {
$result = $rhubarb->task('app.add')
    ->delay($argsPython, array())
    ->get();
} catch (\Rhubarb\Exception\TimeoutException $e) {
    /**
     * If the task result is not received within '10' seconds (default) a
     * [\Rhubarb\Exception\TimeoutException] is thrown. 
     */
    echo $e->getMessage(), PHP_EOL;
}

```

## Determining registered task names and other parameters

Starting a celery worker in debug mode `-l debug` will list the tasks names for easy viewing as well as other
pertinent configuration information i.e. [queues, transport, etc.].

```
$ celery worker -A app -l debug
>>>
 -------------- celery@Zirc v3.1.7 (Cipater)
---- **** -----
--- * ***  * -- Darwin-13.0.0-x86_64-i386-64bit
-- * - **** ---
- ** ---------- [config]
- ** ---------- .> app:         tasks:0x10d171a50
- ** ---------- .> transport:   redis://localhost:6379/0
- ** ---------- .> results:     disabled
- *** --- * --- .> concurrency: 8 (prefork)
-- ******* ----
--- ***** ----- [queues]
 -------------- .> celery           exchange=celery(direct) key=celery


[tasks]
  . celery.backend_cleanup
  . celery.chain
  . celery.chord
  . celery.chord_unlock
  . celery.chunks
  . celery.group
  . celery.map
  . celery.starmap
  . app.add

```

# Documentation

 - [Rhubarb Documentation](http://rhubarb.readthedocs.org/en/latest/)

# Support and Issues

Should you require support please open a github issue at https://github.com/zircote/Rhubarb/issues
