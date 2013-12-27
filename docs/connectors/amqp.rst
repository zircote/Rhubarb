AMQP
====

**Rhubarb** supports **AMQP** by way of `ext-amqp <https://github.com/bkw/pecl-amqp-official>`_

.. note:: Note that at this time the **ext-amqp** extension does not support *TLS*

Configuration
-------------
   
    **Simple AMQP Config**

    .. literalinclude:: ../examples/configuration/amqp.php
       :language: php
       :lines: 1,23-

AMQP supports a number of options listed below:
  - host [string - default: localhost]
  - port [integer - default: 5672] 
  - vhost [string - default: celery]
  - login [string - default: guest]
  - password [string - default: guest]
  - write_timeout [integer - default: -1]
  - read_timeout [integer - default: -1]

These options may be used as an associative array or an URI:

    **URI string Connection definition**

    .. literalinclude:: ../examples/configuration/amqp_uri.php
       :language: php
       :lines: 1,23-

    **Array Connection definition**

    .. literalinclude:: ../examples/configuration/amqp_array.php
       :language: php
       :lines: 1,23-
