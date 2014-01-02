=======
Rhubarb
=======

.. topic:: Examples of Celery Worker Execution From PHP

    **Send AsyncResult and Wait For Result**
     
    .. literalinclude:: examples/delay.php
       :language: php
       :lines: 23-
    
    **Send task with kwargs**
    
    .. literalinclude:: examples/kwargs.php
       :language: php
       :lines: 23-
    
    **Send task using an invokable signature**
    
    .. literalinclude:: examples/invokable_signature.php
       :language: php
       :lines: 23-
    
    **Send task with a 60 second countdown header**
    
    .. literalinclude:: examples/countdown60.php
       :language: php
       :lines: 23-
    
    **Send task using ETA header**
    
    .. literalinclude:: examples/task_eta.php
       :language: php
       :lines: 23-
    
    **Send task using Expires header**
    
    .. literalinclude:: examples/task_expires.php
       :language: php
       :lines: 23-
