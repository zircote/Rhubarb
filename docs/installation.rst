Installation
============

Composer
--------

 The recommended method of installation is via `composer <http://getcomposer.org>`_
  
 .. code-block:: bash
     
    composer require zircote/rhubarb:3.1.*
    
 Depending on your selection of connectors you will also need to require or compile 
 the appropriate extension or libraries.
 
 Extensions may be installed with pecl i.e.

 .. code-block:: bash
    
    pecl install mongo
    
 Libraries can be included utilizing the composer command 
 
 .. code-block:: bash
 
    composer require predis/predis:master-dev
    
PECL AMQP
----------

Development of the Official PHP AMQP extension may be found at https://github.com/bkw/pecl-amqp-official as well as stubs and tests.


Installation via pecl:
    .. code-block:: bash
        sudo pecl install amqp

To build the ext-amqp from source:
    
    .. code-block:: bash
        git clone https://github.com/alanxz/rabbitmq-c
        pushd rabbitmq-c
            git submodule init
            git submodule update
            mkdir bin-rabbitmq-c
            cd bin-rabbitmq-c
            cmake ..
            make
            sudo make install
        popd
        git clone https://github.com/bkw/pecl-amqp-official.git
        pushd pecl-amqp-official
            phpize . && ./configure
            make && make test
            sudo make install
            sudo echo "[amqp]" > $(path_to_php_ini)/conf.d/amqp.ini
            sudo echo "extension=amqp.so" >> $(path_to_php_ini)/conf.d/amqp.ini
        popd
