redis
=====

.. topic:: **Rhubarb** currently supports one `**redis** <http://redis.io>`_ connectors:
 
 - `predis/predis <https://packagist.org/packages/predis/predis>`_
 

predis/predis
--------------

.. topic:: Configuration
 
 The configuration of the *predis/predis* implementation for **Rhubarb** is comprised of the following key hierarchy:
 
     - **broker**
        - **type**: the broker class name without the namespace
        - **options**: the broker specific options
            - **exchange**: the name of the target exchange
            - **uri**: the amqp server/cluster uri (it should match your celery worker configuration)
     - **result_store**
         - **type**: the result_store class name without the namespace
         - **options**: the result_store specific options
            - **exchange**: the name of the target exchange
            - **uri**: the amqp server/cluster uri (it should match your celery worker configuration)
 
 .. note:: These options SHOULD match your celery worker configuration.
 
 .. code-block:: php
 
    $options = array(
        'broker' => array(
            'type' => 'Predis',
            'options' => array(
                'exchange' => 'celery',
                'uri' => 'redis://localhost:6379/1'
            )
        ),
        'result_store' => array(
            'type' => 'Predis',
            'options' => array(
                'exchange' => 'celery',
            )
        )
    );
    
  $rhubarb = new \Rhubarb\Rhubarb($options);


