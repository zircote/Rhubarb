<?php
namespace Rhubarb\Connector;

/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2012] [Robert Allen]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
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
