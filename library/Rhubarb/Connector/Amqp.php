<?php
namespace Rhubarb\Connector;

/**
 * @package     
 * @category    
 * @subcategory 
 */
use AMQP\Connection;

/**
 * @package     
 * @category    
 * @subcategory 
 */
class Amqp implements ConnectorInterface
{

    /**
     * @var Connection
     */
    protected $connection;
    
    /**
     * @var array
     */
    protected $options = array(
        'uri' => 'amqp://guest:guest@localhost:5672/',
        'options' => array()
    );

    /**
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return self
     *
     * @throws \UnexpectedValueException
     */
    public function setOptions(array $options)
    {
        if(isset($options['exchange'])){
            if(!is_string($options['exchange'])){
                throw new \UnexpectedValueException('exchange value is not a string, a string is required');
            }
            $this->exchange = $options['exchange'];
            unset($options['exchange']);
        }
        if(isset($options['queue'])){
            if(isset($options['queue']['arguments'])){
                $this->queueOptions = $options['queue'];
            }
            unset($options['queue']);
        }
        $merged = array('uri' => isset($options['uri']) ? $options['uri'] : $this->options['uri']);
        $merge['options'] = array_merge($this->options['options'], (array) @$options['options']);
        $this->options = $merged;
        return $this;
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        if(!$this->connection){
            $options = $this->getOptions();
            $connection = new Connection($options['uri'], @$options['options'] ?: array());
            $this->setConnection($connection);
        }
        return $this->connection;
    }

    /**
     * @param Connection $connection
     *
     * @return self
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }
}
