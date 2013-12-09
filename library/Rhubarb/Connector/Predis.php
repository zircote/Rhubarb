<?php
namespace Rhubarb\Connector;

/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2013] [Robert Allen]
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
use Rhubarb\Exception\ConnectionException;
use Rhubarb\Rhubarb;
/**
 * @package
 * @category
 * @subcategory
 */
class Predis extends AbstractConnector
{

    const TASK_KEY_PREFIX = 'celery-task-meta-';
    const GROUP_KEY_PREFIX = 'celery-taskset-meta-';
    const CHORD_KEY_PREFIX = 'chord-unlock-';
    /**
     * @var Client
     */
    protected $connection;

    /**
     * @link https://github.com/nrk/predis/wiki/Connection-Parameters
     * 
     * Predis Options:
     *  - scheme [string - default: tcp] [tcp, unix, http]
     *  - host [string - default: 127.0.0.1]
     *  - port [integer - default: 6379]
     *  - path [string - default: not set]
     *  - database [integer - default: not set]
     *  - password [string - default: not set]
     *  - connection_async [boolean - default: false]
     *  - connection_persistent [boolean - default: false]
     *  - connection_timeout [float - default: 5.0]
     *  - read_write_timeout [float - default: not set]
     *  - alias [string - default: not set]
     *  - weight [integer - default: not set]
     *  - iterable_multibulk [boolean - default: false]
     *  - throw_errors [boolean - default: true]
     * 
     * Example URI Usage:
     * tcp://host:port?password=54321&database=0&connection_async=false&connection_persistent=false\
     *      &connection_timeout=5.0&read_write_timeout=&alias=&weight=&iterable_multiblock=false&throw_errors=false
     * unix:///var/run/redis.sock?password=54321&database=0&connection_async=false&connection_persistent=false\
     *      &connection_timeout=5.0&read_write_timeout=&alias=&weight=&iterable_multiblock=false&throw_errors=false
     * 
     * @var array
     */
    protected $options = array(
        'connection' => 'tcp://localhost:6379?database=0'
    );
    protected $properties = array(
        'content_type'=> Rhubarb::CONTENT_TYPE_JSON,
        'content_encoding' => Rhubarb::CONTENT_ENCODING_BASE64
    );

    /**
     * @param array $options
     * @return $this
     * @throws ConnectionException
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    /**
     * @return Client
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $options = $this->getOptions();
            $connection = new Client($options['connection']);
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
