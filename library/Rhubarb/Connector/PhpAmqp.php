<?php
namespace Rhubarb\Connector;

/**
 * @package
 * @category
 * @subcategory
 */
use AMQPConnection;

/**
 * @package
 * @category
 * @subcategory
 */
class PhpAmqp implements ConnectorInterface
{

    /**
     * @var AmqpConnection
     */
    protected $connection;
    /**
     * @var array
     */
    protected $options = array(
        'connection' => array(
            'host' => '127.0.0.1',
            'port' => 5672,
            'vhost' => '/',
            'login' => 'guest',
            'password' => 'guest'
        ),
        'options' => array()
    );

    /**
     * @param array $options
     */
    public function __construct($options = array())
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
        if (isset($options['exchange'])) {
            $this->exchange = $options['exchange'];
        }
        if (isset($options['exchange'])) {
            if (!is_string($options['exchange'])) {
                throw new \UnexpectedValueException('exchange value is not a string, a string is required');
            }
            $this->exchange = $options['exchange'];
            unset($options['exchange']);
        }
        if (isset($options['queue'])) {
            if (isset($options['queue']['arguments'])) {
                $this->queueOptions = $options['queue'];
            }
            unset($options['queue']);
        }
        if (isset($options['uri'])) {
            $uri = parse_url($options['uri']);
            if (!isset($uri['port'])) {
                $uri['scheme'] == 'amqps' ? 5673 : $this->options['connection']['port'];
            } else {
                $port = isset($uri['port']) ? $uri['port'] : $this->options['connection']['port'];
            }
            unset($options['uri']);
            $options['connection']['host'] = $uri['host'];
            $options['connection']['port'] = $port;
            $options['connection']['vhost'] = isset($uri['path']) ? $uri['path'] : $this->options['connection']['path'];
            /* I don't like it but to ensure that all parties are happy this is necasary */
            $options['connection']['vhost'] = preg_replace('#^/#', null, $options['connection']['vhost']);
            $options['connection']['login'] = isset($uri['username']) ? $uri['username'] : $this->options['connection']['login'];
            $options['connection']['password'] = isset($uri['pass']) ? $uri['pass'] : $this->options['connection']['password'];
            $this->options['connection'] = $options['connection'];
        }
        return $this;
    }


    /**
     * @return AMQPConnection
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $options = $this->getOptions();
            $connection = new AMQPConnection($options['connection']);
            $this->setConnection($connection);
        }
        return $this->connection;
    }

    /**
     * @param AMQPConnection $connection
     * @return self
     */
    public function setConnection(AMQPConnection $connection)
    {
        $this->connection = $connection;
        return $this;
    }
}
