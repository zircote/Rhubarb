#!/bin/sh


PHPAMQP_CELERY_COMMAND=nohup celery -A phpamqp worker --workdir=tests/celery/tasks > /dev/null 2>&1
PREDIS_CELERY_COMMAND=nohup celery -A predis worker --workdir=tests/celery/tasks > /dev/null 2>&1
COMPOSER=composer.phar

pushd ..
    rabbitmqctl delete_vhost celery
    rabbitmqctl add_vhost celery
    rabbitmqctl set_permissions -p celery guest ".*" ".*"  ".*"
#    composer require zircote/amqp:*
    (nohup celery -A phpamqp worker --workdir=tests/celery/tasks > /dev/null 2>&1 &)
    sleep 5
    CONNECTOR=amqp vendor/bin/phpunit tests
    pkill -f celery
popd
