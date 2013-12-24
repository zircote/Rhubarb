AMQP
====

.. topic:: **Rhubarb** currently supports two **AMQP** connectors:
 
 - `zircote/amqp <https://packagist.org/packages/zircote/amqp>`_
 - `ext-amqp <https://github.com/bkw/php-amqp>`_
 
.. note:: Note that at this time the **ext-amqp** extension does not support *TLS*; for *TLS* support you will be required to utilize the **zircote/amqp** package.
 

zircote/amqp
------------

.. topic:: Configuration
 
 The configuration of the *zircote/amqp* implementation for **Rhubarb** is comprised of the following key hierarchy:
 
     - **broker**
        - **type**: the broker class name without the namespace
        - **options**: the broker specific options
            - **exchange**: the name of the target exchange
            - **queue**: an array of options related to the queue
                - **name**: the queue name
                - **arguments**: an array of arguments related to the queue, these are AMQP specific and are documented in the *zircote/amqp* library
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
          'type' => 'Amqp',
          'options' => array(
              'exchange' => 'celery',
              'queue' => array(
                  'name' => 'celery',
                  'arguments' => array(
                      'x-ha-policy' => array('S', 'all')
                  )
              ),
              'connection' => 'amqp://guest:guest@localhost:5672/celery'
          )
      ),
      'result_store' => array(
          'type' => 'Amqp',
          'options' => array(
              'exchange' => 'celery',
              'connection' => 'amqp://guest:guest@localhost:5672/celery'
          )
      )
  );
    
  $rhubarb = new \Rhubarb\Rhubarb($options);



ext-amqp
---------


.. topic:: Configuration

 The configuration of the *ext-amqp* implementation for **Rhubarb** is comprised of the following key hierarchy:
 
    - **broker**
        - **type**: the broker class name without the namespace
        - **options**: the broker specific options
            - **exchange**: the name of the target exchange
            - **queue**: an array of options related to the queue
                - **name**: the queue name
                - **arguments**: an array of arguments related to the queue, these are AMQP specific
            - **uri**: the amqp server/cluster uri (it should match your celery worker configuration)
    - **result_store**
        - **type**: the result_store class name without the namespace
        - **options**: the result_store specific options
            - **exchange**: the name of the target exchange
        - **uri**: the amqp server/cluster uri (it SHOULD match your celery worker configuration)
 
 .. note:: These options SHOULD match your celery worker configuration.
 
 .. code-block:: php
 
  $options = array(
      'broker' => array(
          'type' => 'PhpAmqp',
          'options' => array(
              'exchange' => 'celery',
              'queue' => array(
                  'arguments' => array(
                  )
              ),
              'connection' => 'amqp://guest:guest@localhost:5672/celery'
          )
      ),
      'result_store' => array(
          'type' => 'PhpAmqp',
          'options' => array(
              'exchange' => 'celery',
              'connection' => 'amqp://guest:guest@localhost:5672/celery'
          )
      )
  );
  
  $rhubarb = new \Rhubarb\Rhubarb($options);
  


