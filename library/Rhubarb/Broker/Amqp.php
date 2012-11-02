<?php
namespace Rhubarb\Broker;

/**
 * @package     Rhubarb
 * @category    Broker
 */
/**
 * @package     Rhubarb
 * @category    Broker
 *
 * Use:
 *
 * $options = array(
 *  'broker' => array(
 *      'type' => 'Amqp',
 *      'options' => array(
 *          'uri' => 'amqp://celery:celery@localhost:5672/celery'
 *      )
 *  ),
 *  'result_store' => array(
 *      ...
 * );
 * $rhubarb = new \Rhubarb\Rhubarb($options);
 */
class Amqp extends AbstractBroker
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
    
    protected $message = null;

    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    public function publishTask(\Rhubarb\Task $task)
    {
//        array('x-ha-policy' => array('S', 'nodes'),
//              'x-ha-policy-params' => array('A', array('rabbit@host','fox@host'))
//        );
//        array('x-ha-policy' => array('S', 'all'));
        $channel = $this->getConnection()->channel();
        $channel->queueDeclare('celery', false, true, false, false, false, array('x-ha-policy' => array('S', 'all')));
        $channel->exchangeDeclare($this->exchange, 'direct', true, true);
        $channel->queueBind('celery', $this->exchange, $task->getId());
        $msgProperties =  array('content_type' => \Rhubarb\Rhubarb::RHUBARB_CONTENT_TYPE);
        if($task->getPriority()){
            $msgProperties['priority'] = $task->getPriority();
        }
        
        $channel->basicPublish(
            new \AMQP\Message((string) $task,$msgProperties),
            $this->exchange,
            $task->getId()
        );
        $channel->close();
        $channel = null;
    }

    /**
     * @return \AMQP\Connection
     */
    public function getConnection()
    {
        if(!$this->connection){
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
        if(isset($options['exchange'])){
            $this->exchange = $options['exchange'];
        }
        if(isset($options['exchange'])){
            if(!is_string($options['exchange'])){
                throw new \UnexpectedValueException('exchange value is not a string, a string is required');
            }
            $this->exchange = $options['exchange'];
            unset($options['exchange']);
        }
        $merged = array('uri' => isset($options['uri']) ? $options['uri'] : $this->options['uri']);
        $merge['options'] = array_merge($this->options['options'], (array) @$options['options']);
        $this->options = $merged;
        return $this;
    }
}
