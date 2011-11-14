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
    const VERSION     = '20111114';
    const API_VERSION = '1';

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
    private $isConfigured = FALSE;

    /**
     * @var bool
     */
    private $isStarted = FALSE;

    /**
     * @var SessionBackend
     */
    private $backend;

    /**
     * @var int
     */
    protected $lifetime = 300;

    /**
     * @var string
     */
    protected $path = '/';

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var bool
     */
    protected $isSecure = FALSE;

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
     * Configures the session
     *
     * @param string $name Session name
     * @param string $domain Session cookie domain
     * @param string $path Session cookie path
     * @param int $lifetime Session cookie lifetime
     * @param bool $isSecure Whether session is HTTPS or HTTP
     * @return NULL
     */
    public function configure($name, $domain, $path = '/', $lifetime = 300, $isSecure = FALSE)
    {
        if ($this->isStarted()) {
            throw new SessionException('Session has already been started', SessionException::SESSION_ALREADY_STARTED);
        }

        if ($name == '') {
            throw new SessionException('Session name must not be empty', SessionException::EMPTY_SESSION_NAME);
        }

        if ($domain == '') {
            throw new SessionException('Cookie domain must not be empty', SessionException::EMPTY_COOKIE_DOMAIN);
        }

        if (!is_bool($isSecure)) {
            throw new SessionException('Boolean value expected', SessionException::BOOLEAN_VALUE_EXPECTED);
        }

        $this->name = $name;
        $this->lifetime = $lifetime;
        $this->path = $path;
        $this->domain = $domain;
        $this->isSecure = $isSecure;
        
        $this->isConfigured = TRUE;
    }

    /**
     * Starts the sesssion
     *
     * @return NULL
     * @throws spriebsch\session\SessionException Session has already been started
     */
    public function start()
    {
        if ($this->isStarted()) {
            throw new SessionException('Session has already been started', SessionException::SESSION_ALREADY_STARTED);
        }

        if (!$this->isConfigured) {
            throw new SessionException('Session has not been configured', SessionException::SESSION_NOT_CONFIGURED);
        }

        $this->backend->startSession($this->name, $this->lifetime, $this->path, $this->domain, $this->isSecure, TRUE);
        $this->data = $this->backend->read();
        $this->isStarted = TRUE;
    }

    /**
     * Returns the sesssion id
     *
     * @return string
     */
    public function getId()
    {
        $this->ensureSessionIsStarted();
        return $this->backend->getSessionId();
    }

    /**
     * Returns the session name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Regenerates the session id to prevent session fixations
     *
     * @return string new session id
     */
    public function regenerateId()
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
    public function commit()
    {
        $this->ensureSessionIsStarted();
        $this->backend->write($this->data);
    }

    /**
     * Destroy the session.
     *
     * @return NULL
     */
    public function destroy()
    {
        $this->ensureSessionIsStarted();
        $this->backend->destroy();
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
     * Returns a session variable
     *
     * @param mixed $key Session variable name
     * @return mixed Session variable value
     * @throws spriebsch\session\SessionException Unknown session variable $key
     */
    final protected function get($key)
    {
        if (!$this->has($key)) {
            throw new SessionException('Unknown session variable "' . $key . '"', SessionException::UNKNOWN_SESSION_VARIABLE);
        }

        return $this->data[$key];
    }

    /**
     * Checks whether the session has already been started
     *
     * @return bool
     */
    final protected function isStarted()
    {
        return $this->isStarted;
    }

    /**
     * Makes sure that the session is started, and throws exception otherwise
     *
     * @return NULL
     * @throws spriebsch\session\SessionException
     */
    final protected function ensureSessionIsStarted()
    {
        if (!$this->isStarted()) {
            throw new SessionException('Session has not been started', SessionException::SESSION_NOT_STARTED);
       }
    }
}
