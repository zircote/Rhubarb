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
use AMQP\Connection;

/**
 * @package     
 * @category    
 * @subcategory 
 */
class Amqp extends AbstractConnector
{

    /**
     * @var Connection
     */
    protected $connection;
    
    /**
     * @var array
     */
    protected $options = array(
        'uri' => 'amqp://guest:guest@localhost:5672/celery',
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
        $merged = array('uri' => isset($options['connection']) ? $options['connection'] : $this->options['uri']);
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
