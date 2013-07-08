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
class Mongo implements ConnectorInterface
{

    const CELERY_MESSAGES_COLLECTION = 'celery';
    const CELERY_TASK_META = 'celery_taskmeta';
    /**
     * @var \MongoDB
     */
    protected $connection;
    /**
     * @var string
     */
    protected $db = 'celery';
    
    /**
     * @var array
     */
    protected $options = array(
        'uri' => 'mongodb://localhost:27017/celery',
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
        $merge['options'] = array_merge($this->options['options'], (array) @$options['options']);
        $merge['uri'] = $this->parseUri($this->options['uri']);
        $this->options = $merge;
        return $this;
    }

    /**
     * @param string $uri
     * @return string
     */
    protected function parseUri($uri)
    {
        $auth = null;
        $uri = parse_url($uri);
        $this->db = trim($uri['path'], '/');
        if (isset($uri['username'])) {
            $auth = sprintf(
                '%s%s',
                $uri['username'],
                isset($uri['pass']) ? $uri['pass'] : null
            );
        }
        $uri =sprintf(
            '%s://%s%s:%s',
            isset($uri['scheme']) ? $uri['scheme'] : 'mongodb',
            $auth,
            isset($uri['host']) ? $uri['host'] : 'localhost',
            isset($uri['port']) ? $uri['port'] : 27017
        );
        return $uri;
    }
    /**
     * @return \MongoDB
     */
    public function getConnection()
    {
        /*
         * @todo add logging
         */
        if(!$this->connection){
            $options = $this->getOptions();
            $uri = $options['uri'];
            unset($options['uri']);
            $client = new \MongoClient($uri, isset($options['options']) ? $options['options'] : array());
            $client->connect();
            $connection = $client->selectDB($this->db);
            $this->setConnection($connection);
        }
        return $this->connection;
    }

    /**
     * @param \MongoDB $connection
     *
     * @return self
     */
    public function setConnection(\MongoDB $connection)
    {
        $this->connection = $connection;
        return $this;
    }
}
