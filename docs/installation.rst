Installation
============

Composer
--------

The recommended method of installation is via `composer <http://getcomposer.org>`_
  
 .. code-block:: bash
     
    composer require 'zircote/rhubarb=3.2-dev'
    
Depending on your selection of connectors you will also need to require or compile the appropriate extension or libraries.
    
 Libraries can be included utilising the composer command 
 
 .. code-block:: bash
 
    composer require 'predis/predis'
    

PECL AMQP
---------

The Official PHP AMQP extension may be found at https://github.com/bkw/pecl-amqp-official as well as stubs and tests.

  **Installation via pecl**

  .. code-block:: bash

     sudo pecl install amqp


  **To build the ext-amqp from source:**
  
  .. literalinclude:: examples/pecl-amqp-installation.sh
     :language: sh
