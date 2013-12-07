<?php
namespace Rhubarb\Connector;

/**
 * @package
 * @category
 * @subcategory
 */
use Predis\Client;
use Rhubarb\Exception\CeleryConfigurationException;

/**
 * @package
 * @category
 * @subcategory
 */
class Predis
{

    /**
     * @var \Predis\Client
     */
    protected $connection;

    /**
     * @var array
     */
    protected $options = array(
        'connection' => 'redis://localhost:6379/0',
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
        if (isset($options['connection'])) {
            $uri = parse_url($options['connection']);
            unset($options['connection']);
            if (isset($uri['scheme']) && $uri['scheme'] === 'redis') {
                $uri['scheme'] = $uri['scheme'] == 'unix' ? : 'tcp';
            }
            if (isset($uri['path'])) {
                $uri['database'] = trim($uri['path'], '/');
                $options['connection']['database'] = isset($uri['databsae']) ? $uri['database'] : null;
            }
            $options['connection']['host'] = $uri['host'];
            $options['connection']['port'] = isset($uri['port']) ? $uri['port'] : 6379;
            $options['connection']['login'] = isset($uri['username']) ? $uri['username'] : null;
            $options['connection']['password'] = isset($uri['pass']) ? $uri['pass'] : null;
            $uri = null;
            $this->options['connection'] = $options['connection'];
        }
        return $this;
    }

    /**
     * @return \Predis\Client
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $options = $this->getOptions();
            $options['connection'] = preg_replace('/redis\:/', 'tcp:', $options['connection']);
            $connection = new Client($options['connection'], @$options['options'] ? : array());
            $this->setConnection($connection);
        }
        return $this->connection;
    }

    /**
     * @param Client $connection
     * @return self
     */
    public function setConnection(Client $connection)
    {
        $this->connection = $connection;
        return $this;
    }
}
