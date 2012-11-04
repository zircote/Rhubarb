<?php
namespace Rhubarb\ResultStore;

/**
 * @package     Rhubarb
 * @category    ResultStore
 */
/**
 * @package     Rhubarb
 * @category    ResultStore
 *
 * Use:
 *
 * $options = array(
 *  'broker' => array(
 *  ...
 *  ),
 *  'result_store' => array(
 *      'type' => 'Amqp',
 *      'options' => array(
 *          'uri' => 'amqps://celery:celery@localhost:5671/celery_results',
 *          'options' => array(
 *              'ssl_options' => array(
 *                  'verify_peer' => true,
 *                  'allow_self_signed' => true,
 *                  'cafile' => 'some_ca_file'
 *                  'capath' => '/etc/ssl/ca,
 *                  'local_cert' => /etc/ssl/self/key.pem'
 *              ),
 *          )
 *      )
 *  )
 * );
 * $rhubarb = new \Rhubarb\Rhubarb($options);
 */
class Amqp extends AbstractResultStore
{
    /**
     * @var \AMQP\Connection
     */
    protected $connection;
    /**
     * @var array
     */
    protected $options = array(
        'uri' => 'amqp://guest:guest@localhost:5672/',
        'options' => array()
    );

    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    public function getTaskResult(\Rhubarb\Task $task)
    {
        try {
            $result = null;
            $taskId = str_replace('-','', $task->getId());
            $channel = $this->getConnection()->channel();
            if ($message = $channel->basicGet(array('queue' => $taskId))) {
                $messageBody = json_decode($message->body);
                if (json_last_error()) {
                    throw new \Rhubarb\Exception\InvalidJsonException('Serialization Error, result is not valid JSON');
                }
                $channel->basicAck($message->delivery_info['delivery_tag']);
                $channel->queueDelete(
                    array( 'queue' => $taskId, 'if_unused' => true, 'if_empty' => true, 'no_wait' => true)
                );
                $channel->close();
                $result = $messageBody;
            }
            return $result;
        } catch (\AMQP\Exception\ChannelException $e){
            return $result;
        }
    }

    /**
     * @return \AMQP\Connection
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $options = $this->getOptions();
            $connection = new \AMQP\Connection($options['uri'], @$options['options'] ?: array());
            $this->setConnection($connection);
        }
        return $this->connection;
    }

    /**
     * @param \AMQP\Connection $connection
     *
     * @return AMQP
     */
    public function setConnection(\AMQP\Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @param array $options
     *
     * @return AMQP
     *
     * @throws \UnexpectedValueException
     */
    public function setOptions(array $options)
    {
        if (isset($options['result_store'])) {
            $this->resultsExchange = $options['result_store'];
        }
        if(isset($options['exchange'])){
            if(!is_string($options['exchange'])){
                throw new \UnexpectedValueException('exchange value is not a string, a string is required');
            }
            $this->resultsExchange = $options['exchange'];
            unset($options['exchange']);
        }
        $merged = array('uri' => isset($options['uri']) ? $options['uri'] : $this->options['uri']);
        $merge['options'] = array_merge($this->options['options'], (array) @$options['options']);
        $this->options = $merged;
        return $this;
    }
}
