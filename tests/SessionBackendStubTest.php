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
use spriebsch\session\SessionBackendStub;

/**
 * Unit tests for the stub session backend.
 *
 * @author Stefan Priebsch <stefan@priebsch.de>
 * @copyright Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 *
 * @covers spriebsch\session\SessionBackendStub
 */
class SessionBackendStubTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var spriebsch\session\SessionBackendInterface
     */
    protected $backend;

    /**
     * Prepares the test fixture
     *
     * @return NULL
     */
    protected function setUp()
    {
        $this->backend = new SessionBackendStub();
    }

    /**
     * Destroys the test fixture
     *
     * @return NULL
     */
    protected function tearDown()
    {
        unset($this->backend);
    }

    /**
     * @covers spriebsch\session\SessionBackendStub::startSession
     * @covers spriebsch\session\SessionBackendStub::getSessionId
     * @covers spriebsch\session\SessionBackendStub::regenerateSessionId
     */
    public function testRegenerateIdChangesSessionId()
    {
        $this->backend->startSession('', '', '', '');
        $id = $this->backend->getSessionId();
        $this->backend->regenerateSessionId();
        
        $this->assertNotEquals($this->backend->getSessionId(), $id);
    }
    
    /**
     * @covers spriebsch\session\PhpSessionBackend::write
     */
    public function testWriteWritesToSessionSuperglobal()
    {
        $this->backend->startSession('', '', '', '');
        $this->backend->write(array('foo' => 'another-foo'));

        $this->assertEquals(array('foo' => 'another-foo'), $this->backend->read());
    }    

    /**
     * @covers spriebsch\session\PhpSessionBackend::destroy
     */
    public function testDestroyDestroysSession()
    {
        $this->backend->startSession('', '', '', '');
        $this->backend->destroy();

        $this->assertEmpty($this->backend->getSessionId());
    }
}
