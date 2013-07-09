Installation
============

Composer
--------

 The recommended method of installation is via `composer <http://getcomposer.org>`_
  
 .. code-block:: bash
     
    composer require zircote/rhubarb:0.2.*
    
 Depending on your selection of connectors you will also need to require or compile 
 the appropriate extension or libraries.
 
 Extensions may be installed with pecl i.e.

 .. code-block:: bash
    
    pecl install mongo
    
 Libraries can be included utilizing the composer command 
 
 .. code-block:: bash
 
    composer require predis/predis:master-dev
 
 
