#!/bin/sh

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
