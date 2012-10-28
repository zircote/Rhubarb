Rhubarb
=======

A PHP Job Submission library for [Celery](http://celeryproject.org/)

Setup:
======

```
git clone git://github.com/zircote/Rhubarb.git
composer install
```
Add `%project%/vendor/autoload.php` to your bootstrap.

Example:
========
Using the [Celery Docs `add` example](http://docs.celeryproject.org/en/latest/getting-started/first-steps-with-celery.html#application):

```php

    $options = array(
        'broker' => array(
            'type' => 'Amqp',
            'options' => array(
                'uri' => 'amqp://celery:celery@localhost:5672/celery'
            )
        )
    );
    $rhubarb = new \Rhubarb\Rhubarb($options);

    $result = $rhubarb->sendTask('proj.tasks.add', array(2, 2));
    $timeout = 10; // seconds
    $checkInterval = 0.2; //test for result every 200ms
    var_dump($result->get($timeout, $checkInterval));
    // int(5)
```

Multiple Broker Support
=========================

```php

    $options = array(
        'broker' => array(
            'type' => 'Amqp',
            'options' => array(
                'uri' => 'amqp://celery:celery@localhost:5672/celery'
            )
        ),
        'result_broker' => array(
            'type' => 'Amqp',
            'options' => array(
                'uri' => 'amqp://celery:celery@localhost:5672/celery_results'
            )
        )
    );
    $rhubarb = new \Rhubarb\Rhubarb($options);

    $result = $rhubarb->sendTask('proj.tasks.add', array(2, 2));
    $timeout = 10; // seconds
    $checkInterval = 0.2; //test for result every 200ms
    var_dump($result->get($timeout, $checkInterval));
    // int(5)

```
It is very basic at this point, with plans to add more features in due time.
