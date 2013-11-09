Mongo
=====

.. topic:: **Rhubarb** utilizes the `MongoDB <http://mongodb.org>`_ pecl extension
 
 - `ext-mongo <http://pecl.php.net/package/mongo>`_
 

ext-mongo
------------

.. topic:: Configuration
 
 The configuration of the *ext-mongo* implementation for **Rhubarb** is comprised of the following key hierarchy:
 
     - **broker**
        - **type**: the broker class name without the namespace
        - **options**: the broker specific options
            - **exchange**: the name of the target exchange
            - **uri**: the mongodb host port and db to connect to
     - **result_store**
         - **type**: the result_store class name without the namespace
         - **options**: the result_store specific options
            - **queue**: the queue name
            - **exchange**: the name of the target exchange
            - **uri**: the mongodb host port and db to connect to
 
 .. note:: These options SHOULD match your celery worker configuration.
 
 .. code-block:: php
 
    $options = array(
        'broker' => array(
            'type' => 'Mongo',
            'options' => array(
                'exchange' => 'celery',
                'queue' => 'celery',
                'uri' => 'mongodb://localhost:26017/test
            )
        ),
        'result_store' => array(
            'type' => 'Mongo',
            'options' => array(
                'exchange' => 'celery',
            )
        )
    );
    
  $rhubarb = new \Rhubarb\Rhubarb($options);


