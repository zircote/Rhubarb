from celery import Celery

celery = Celery('tasks', broker='amqp://guest:guest@localhost//celery', backend='amqp://guest:guest@localhost//celery')

celery.conf.update(
    CELERY_TASK_SERIALIZER='json',
    CELERY_RESULT_SERIALIZER='json',
    CELERY_TIMEZONE='America/Chicago',
    CELERY_ENABLE_UTC=True,
    CELERY_ROUTES={
        'phpamqp.subtract': {
            'queue': 'subtract_queue'
        },
        'phpamqp.add': {
            'queue': 'celery'
        }
    },
    CELERY_QUEUES={
        "subtract_queue": {
            "exchange": "subtract_queue"
        },
        "celery": {
            "exchange": "celery"
        }
    }
)

@celery.task
def add(x, y):
    return x + y

@celery.task
def subtract(x, y):
    return x - y
