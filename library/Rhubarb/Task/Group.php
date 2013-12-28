<?php
namespace Rhubarb\Task;

/**
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * Copyright [2012-2014], [Robert Allen]
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
use Rhubarb\Rhubarb;

/**
 * @package
 * @category
 * @subcategory
 * @codeCoverageIgnore
 */
class Group extends Signature
{
    /**
     * 
     */
    const NAME = 'celery.group';

    /**
     * @var array
     */
    protected $group = array();

    /**
     * @param Rhubarb $rhubarb
     * @param array $group
     * @param array $properties
     * @param array $headers
     */
    public function __construct(Rhubarb $rhubarb, $group = array(), $properties = array(), $headers = array())
    {
        parent::__construct($rhubarb, self::NAME, null, $properties, $headers);
        $this->setGroup($group);
    }

    /**
     * @param $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }
}
 
