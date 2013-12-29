Signature
=========


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
