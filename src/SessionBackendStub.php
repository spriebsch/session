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
 * Session stub that works in-memory to ease session-based tests without
 * any external dependencies or modifications of the global state.
 *
 * @author     Stefan Priebsch <stefan@priebsch.de>
 * @copyright  Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 */
class SessionBackendStub implements SessionBackendInterface
{
    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var array
     */
    protected $data = array();

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
        $this->sessionId = $this->getRandomId();
    }
    
    public function getSessionId()
    {
        return $this->sessionId;
    }
    
    public function regenerateSessionId()
    {
        $this->sessionId = $this->getRandomId();
    }

    public function read()
    {
        return $this->data;
    }

    public function write(array $data)
    {
        $this->data = $data;
    }

    public function destroy()
    {
        $this->sessionId = NULL;
    }
    
    protected function getRandomId()
    {
        return md5(uniqid());
    }
}
