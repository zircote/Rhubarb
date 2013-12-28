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
use Rhubarb\Exception\TaskSignatureException;
use Rhubarb\Task\Body\BodyInterface;
use Rhubarb\Rhubarb;

/**
 * @package
 * @category
 * @subcategory
 * @codeCoverageIgnore
 */
class Chord extends Signature
{
    /**
     * 
     */
    const NAME = 'celery.chord';

    /**
     * @var Group
     */
    protected $group;

    /**
     * @param Rhubarb $rhubarb
     * @param array|Signature|Group $group
     * @param BodyInterface $body
     * @param array $properties
     * @param array $headers
     *
     * @throws TaskSignatureException
     */
    public function __construct(Rhubarb $rhubarb, $group = array(), BodyInterface $body = null, $properties = array(), $headers = array())
    {
        parent::__construct($rhubarb, self::NAME, null, $properties, $headers);
        if ($group instanceof Group) {
            $this->group = $group;
        } elseif ($group instanceof Signature) {
            $this->group = new Group($rhubarb, array($group));
        } elseif (is_array($group) && $group) {
            $this->group = new Group($rhubarb, $group);
        }
    }
}
 
