=====
Tasks
=====


Args
====

.. topic:: Arguments


    **Why is the Args pattern complicated?**
        Because, in version 3.2 of Celery message protocol v.2 defines support for language based task routing. To facilitate 
        this for future use and expansion it become necessary to define argument objects that are language specific.

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

Signature
=========

.. topic:: Task Signatures

    Signatures may be created by calling the ``\\Rhubarb\\Rhubarb::task`` method. Providing the ``name``, optional 
    arguments, properties and headers will return a signature. You may use this signature in various ways in your workflow;
    either as means to call the task or as a template for many tasks.

    .. literalinclude:: examples/signatures.php
       :language: php
       :lines: 30-31
    
    .. literalinclude:: examples/signatures.php
       :language: php
       :lines: 34-36
    
    This example demonstrates how you may access the properties, args and headers defined within the signature.
    
    .. literalinclude:: examples/signatures.php
       :language: php
       :lines: 40-51
    
    
    Executing the signature  may be down in two ways ``delay`` or ``applyAsync``. ``delay`` is a wrapper of the other
    to provide familiarity with the Celery API.
    
    
    .. literalinclude:: examples/signatures.php
       :language: php
       :lines: 54-62

Chains
======

.. topic:: Task Chains
    
    **Creating a task chain Example 1**
     
    .. literalinclude:: examples/chain_ex1.php
       :language: php
       :lines: 23-
    
    **Creating a task chain Example 2**
     
    .. literalinclude:: examples/chain_ex2.php
       :language: php
       :lines: 23-


Groups
======

.. topic:: Task Groups

    TBD

Chords
======

.. topic:: Task Chords

    TBD
