<?php
/**
 * Copyright (c) 2011 Stefan Priebsch <stefan@priebsch.de>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 *   * Redistributions of source code must retain the above copyright notice,
 *     this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright notice,
 *     this list of conditions and the following disclaimer in the documentation
 *     and/or other materials provided with the distribution.
 *
 *   * Neither the name of Stefan Priebsch nor the names of contributors
 *     may be used to endorse or promote products derived from this software
 *     without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER ORCONTRIBUTORS
 * BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    session
 * @author     Stefan Priebsch <stefan@priebsch.de>
 * @copyright  Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 * @license    BSD License
 */

namespace spriebsch\session;

/**
 * Keeps applications independent from PHP session funcionality.
 *
 * @author     Stefan Priebsch <stefan@priebsch.de>
 * @copyright  Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 */
abstract class AbstractSession implements SessionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $data = array();

    /**
     * @var bool
     */
    private $isStarted = false;

    /**
     * @var SessionBackend
     */
    private $backend;

    /**
     * Constructs the object
     *
     * @param SessionBackendInterface $backend
     * @return NULL
     */
    public function __construct(SessionBackendInterface $backend)
    {
        $this->backend = $backend;
    }

    /**
     * Sets a session variable
     *
     * @param string $key Session variable name
     * @param mixed $value Session variable value
     * @return NULL
     */
    final protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Returns a session variable
     *
     * @param mixed $key Session variable name
     * @return mixed Session variable value
     * @throws spriebsch\session\SessionException Unknown session variable $key
     */
    final protected function get($key)
    {
        if (!isset($this->data[$key])) {
            throw new SessionException('Unknown session variable "' . $key . '"');
        }

        return $this->data[$key];
    }

    /**
     * Checks whether a session variable exists
     *
     * @param string $key Session variable name
     * @return bool
     */
    final protected function has($key)
    {
        return isset($this->data[$key]);
    }
    
    /**
     * Starts the sesssion
     *
     * @return NULL
     * @throws spriebsch\session\SessionException Session has already been started
     */
    final public function start($name)
    {
        if ($this->isStarted()) {
            throw new SessionException('Session has already been started');
        }

        $this->setName($name);
        $this->backend->startSession($name);
        $this->data = $this->backend->read();
        $this->isStarted = TRUE;
    }

    /**
     * Checks whether the session has already been started
     *
     * @return bool
     */
    final public function isStarted()
    {
        return $this->isStarted;
    }

    /**
     * Returns the sesssion id
     *
     * @return string
     */
    final public function getId()
    {
        $this->ensureSessionIsStarted();
        return $this->backend->getSessionId();
    }

    /**
     * Returns the session name
     *
     * @return string
     */
    final public function getName()
    {
        return $this->name;
    }

    /**
     * Regenerates the session id to prevent session fixations
     *
     * @return string new session id
     */
    final public function regenerateId()
    {
        $this->ensureSessionIsStarted();
        $this->backend->regenerateSessionId();
        return $this->getId();
    }

    /**
     * Writes session data
     *
     * @return NULL
     */
    final public function commit()
    {
        $this->ensureSessionIsStarted();
        $this->backend->write($this->data);
    }

    /**
     * Destroy the session.
     *
     * @return NULL
     */
    final public function destroy()
    {
        $this->ensureSessionIsStarted();
        $this->backend->destroy();
    }

    /**
     * Makes sure that the session is started, and throws exception otherwise
     *
     * @return NULL
     * @throws spriebsch\session\SessionException
     */
    protected function ensureSessionIsStarted()
    {
        if (!$this->isStarted()) {
            throw new SessionException('Session has not been started');
        }
    }

    /**
     * Sets the session name
     *
     * @param string $name
     * @return NULL
     */
    final protected function setName($name)
    {
        if ($name == '') {
            throw new SessionException('Session name required');
        }
    
        $this->name = $name;
    }
}
