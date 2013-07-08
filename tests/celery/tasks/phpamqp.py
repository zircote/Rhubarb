from celery import Celery

celery = Celery('tasks', broker='amqp://guest:guest@localhost//celery', backend='amqp://guest:guest@localhost//celery')

celery.conf.update(
    CELERY_TASK_SERIALIZER='json',
    CELERY_RESULT_SERIALIZER='json',
    CELERY_TIMEZONE='America/Chicago',
    CELERY_ENABLE_UTC=True,
)

@celery.task
def add(x, y):
    return x + y
