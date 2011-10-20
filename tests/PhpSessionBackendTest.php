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

namespace spriebsch\session\tests;

use PHPUnit_Framework_TestCase;
use spriebsch\session\PhpSessionBackend;

/**
 * Unit tests for the Session class. Tests must be run in separate processes
 * because the built-in session functionality changes the global state.
 *
 * @author Stefan Priebsch <stefan@priebsch.de>
 * @copyright Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 *
 * @runTestsInSeparateProcesses
 */
class PhpSessionBackendTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var spriebsch\session\SessionBackendInterface
     */
    protected $backend;

    protected $sessionName = 'a-session-name';

    /**
     * Prepares the test fixture.
     *
     * @return NULL
     */
    protected function setUp()
    {
        $this->backend = new PhpSessionBackend();

        if (headers_sent()) {
            $this->markTestSkipped('Headers already sent');
        }

        if (isset($_SESSION)) {
            $this->markTestSkipped('Session already started');
        }
    }

    /**
     * Destroys the test fixture.
     *
     * @return NULL
     */
    protected function tearDown()
    {
        unset($this->backend);
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::startSession
     */
    public function testStartSessionSetsSessionName()
    {
        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');

        $this->assertEquals($this->sessionName, session_name());
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::startSession
     */
    public function testStartSessionStartsASession()
    {
        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');

        $this->assertTrue(isset($_SESSION));
        $this->assertNotEmpty(session_id());
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::startSession
     */
    public function testStartSessionSetsCookie()
    {
        if (!in_array('xdebug', get_loaded_extensions())) {
            $this->markTestSkipped('xdebug not available');
        }

        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');

        $headers = xdebug_get_headers();
        $header = $headers[0];
        $this->assertContains('Set-Cookie:', $header);
        $this->assertContains($this->sessionName . '=', $header);
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::getSessionId
     */
    public function testGetSessionIdReturnsSessionId()
    {
        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');

        $this->assertEquals(session_id(), $this->backend->getSessionId());
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::regenerateSessionId
     */
    public function testRegenerateIdChangesSessionId()
    {
        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');
        $id = $this->backend->getSessionId();
        $this->backend->regenerateSessionId();

        $this->assertNotEquals(session_id(), $id);
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::read
     */
    public function testReadReadsFromSessionSuperglobal()
    {
        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');
        $_SESSION['foo'] = 'a-foo';

        $this->assertEquals(array('foo' => 'a-foo'), $this->backend->read());
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::write
     */
    public function testWriteWritesToSessionSuperglobal()
    {
        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');
        $this->backend->write(array('foo' => 'another-foo'));

        $this->assertEquals(array('foo' => 'another-foo'), $_SESSION);
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::destroy
     */
    public function testDestroyDestroysSession()
    {
        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');
        $this->backend->destroy();

        $this->assertEmpty(session_id());
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::destroy
     */
    public function testDestroyUnsetsSessionSuperglobal()
    {
        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');
        $this->backend->destroy();

        $this->assertFalse(isset($_SESSION));
    }

    /**
     * @covers spriebsch\session\PhpSessionBackend::destroy
     */
    public function testDestroySetsCookie()
    {
        if (!in_array('xdebug', get_loaded_extensions())) {
            $this->markTestSkipped('xdebug not available');
        }

        $this->backend->startSession($this->sessionName, 300, '/', '.example.com');
        $this->backend->destroy();

        $headers = xdebug_get_headers();
        $header = array_pop($headers);

        $this->assertContains('Set-Cookie:', $header);
        $this->assertContains($this->sessionName . '=deleted', $header);
    }
}
