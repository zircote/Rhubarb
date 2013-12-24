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
 */
class Chain
{
    /**
     * @var array
     */
    protected $chain = array();

    /**
     * @param array $chain
     * @throws \Rhubarb\Exception\TaskSignatureException
     */
    public function setChain(array $chain)
    {
        foreach (func_get_args() as $signature) {
            if (!$signature instanceof Signature) {
                throw new TaskSignatureException(
                    sprintf('Chains may only be built by Signature types [%s] provide', gettype($signature))
                );
            }
            $this->push($signature);
        }
    }

    /**
     * @param Signature $signature
     * @return $this
     */
    public function push(Signature $signature)
    {
        array_push($this->chain, $signature);
        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->chain = array();
        return $this;
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        $this->setChain(func_get_args());
        return $this;
    }
}
 
