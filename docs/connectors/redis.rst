redis
=====

**Rhubarb** supports `redis <http://redis.io>`_ by way of `predis/predis <https://packagist.org/packages/predis/predis>`_

Configuration
-------------
  
    **Simple Predis Config**

    .. literalinclude:: ../examples/configuration/predis.php
       :language: php
       :lines: 1,23-

Predis supports a number of options listed below:
  - scheme [string - default: tcp] [tcp, unix, http]
  - host [string - default: 127.0.0.1]
  - port [integer - default: 6379]
  - path [string - default: not set]
  - database [integer - default: not set]
  - password [string - default: not set]
  - connection_async [boolean - default: false]
  - connection_persistent [boolean - default: false]
  - connection_timeout [float - default: 5.0]
  - read_write_timeout [float - default: not set]
  - alias [string - default: not set]
  - weight [integer - default: not set]
  - iterable_multibulk [boolean - default: false]
  - throw_errors [boolean - default: true]

Details on the configuration may be found at https://github.com/nrk/predis/wiki/Connection-Parameters

    **Example Usage**

    .. literalinclude:: ../examples/configuration/predis_uri.php
       :language: php
       :lines: 1,23-
