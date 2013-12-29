Args
====

Why is the Args pattern complicated?
    Because, in version 3.2 of Celery message protocol v.2 defines support for language based task routing. To facilitate 
    this for future use and expansion it become necessary to define argument objects that are language specific.

Argument Usage
--------------
    While support for multiple argument formats is possible, current workers only support ``Python`` args.

**Basic Args creation with factory**
 
.. literalinclude:: examples/args.php
   :language: php
   :lines: 24-29

**Explicit Python Args using star args and kwargs**
 
.. literalinclude:: examples/args.php
   :language: php
   :lines: 32-36

**Extended \Rhubarb\Task\Args\Python\Kwargs usage**
 Note the object property and array access usage for flexibility.
 
.. literalinclude:: examples/args.php
   :language: php
   :lines: 39-42

**Creating your own Arg types**
 - Creating your own arg types is as simple as creating an object implementing the ``\Rhubarb\Task\ArgsInterface``
 - Registering it with the ``\Rhubarb\Task\Args`` object
 - Fetching it with the factory

.. literalinclude:: examples/args.php
   :language: php
   :lines: 45-87
