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
use Rhubarb\Message\Message;
use Rhubarb\Rhubarb;

/**
 * @package
 * @category
 * @subcategory
 * @codeCoverageIgnore
 */
class Chain extends Message
{
    /**
     * @var array
     */
    protected $chain = array();


    /**
     * @param Rhubarb $rhubarb
     * @param array $chain
     */
    public function __construct(Rhubarb $rhubarb, $chain = array())
    {
        $this->setRhubarb($rhubarb);
        $this->setChain($chain);
    }

    /**
     * @param array|Signature $chain
     * @throws \Rhubarb\Exception\TaskSignatureException
     */
    public function setChain($chain)
    {
        foreach (func_get_args() as $signature) {
            if (is_array($signature)) {
                foreach ($signature as $sig) {
                    $this->push($sig);
                }
            } elseif (!$signature instanceof Signature) {
                throw new TaskSignatureException(
                    sprintf('Chains may only be built by Signature types [%s] provide', gettype($signature))
                );
            } else {
                $this->push($signature);
            }
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
     * @return AsyncResult
     */
    public function __invoke()
    {
        $this->setChain(func_get_args());
        return $this->dispatch();
    }
}
 
