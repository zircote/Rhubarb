<?php
namespace Rhubarb;

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
 * @package     Rhubarb
 * @category    Rhubarb
 * @subcategory Message
 */

/**
 * @package     Rhubarb
 * @category    Rhubarb
 * @subcategory Message
 */
class Message
{
    const MESSAGE_V1 = '\Rhubarb\Message\V1';
    const MESSAGE_V2 = '\Rhubarb\Message\V2';

    /**
     * @var \Rhubarb\Message\AbstractMessage
     */
    protected $message;

    /**
     * @param Rhubarb $rhubarb
     * @param string $messageClass
     */
    public function __construct(Rhubarb $rhubarb, $messageClass=self::MESSAGE_V1)
    {
        
    }

    /**
     *
     * @param \Rhubarb\Message\V1|\Rhubarb\Message\V2 $message
     * @return Message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return \Rhubarb\Message\V1|\Rhubarb\Message\V2
     */
    public function getMessage()
    {
        return $this->message;
    }
}
