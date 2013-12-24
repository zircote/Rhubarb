<?php
namespace Rhubarb\Task;

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
use Rhubarb\Exception\TaskSignatureException;

/**
 * @package
 * @category
 * @subcategory
 * @codeCoverageIgnore
 */
class Chord
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * @var Signature
     */
    protected $chordSignature;

    /**
     * @throws TaskSignatureException
     * @param $group
     * @param Signature $chordSignature
     */
    public function __construct($group, Signature $chordSignature = null)
    {
        if ($group instanceof Group) {
            $this->group = $group;
        } elseif ($group instanceof Signature) {
            $this->group = new Group(array($group));
        } elseif (is_array($group)) {
            $this->group = new Group($group);
        } else {
            throw new TaskSignatureException;
        }
        $this->chordSignature = $chordSignature;
    }
}
 
