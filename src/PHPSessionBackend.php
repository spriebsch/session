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
 * Uses built-in PHP session functionality
 *
 * @author     Stefan Priebsch <stefan@priebsch.de>
 * @copyright  Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 */
class PHPSessionBackend implements SessionBackendInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $lifetime;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var bool
     */
    protected $isSecure;

    /**
     * Starts the session
     *
     * @param string $name     Session name
     * @param string $domain   Session cookie domain
     * @param string $path     Session cookie path
     * @param int    $lifetime Session cookie lifetime
     * @param bool   $isSecure Whether session is HTTPS or HTTP
     * @return NULL
     */
    public function startSession($name, $lifetime, $path, $domain, $isSecure = FALSE)
    {
        $this->name = $name;
        $this->lifetime = $lifetime;
        $this->path = $path;
        $this->domain = $domain;
        $this->isSecure = $isSecure;

        session_set_cookie_params($lifetime, $path, $domain, $isSecure, TRUE);
        session_name($name);
        session_start();
    }
    
    public function getSessionId()
    {
        return session_id();
    }
    
    public function regenerateSessionId()
    {
        session_regenerate_id(TRUE);
    }

    public function read()
    {
        return $_SESSION;
    }

    public function write(array $data)
    {
        $_SESSION = $data;
    }

    public function destroy()
    {
        setcookie($this->name, '', 1, $this->path, $this->domain, $this->isSecure, TRUE);
        session_destroy();
        unset($_SESSION);
    }
}
