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

use spriebsch\session\tests\stubs\ConcreteSession;

/**
 * Unit tests for the Session class. Tests must be run in separate processes
 * because the built-in session functionality changes the global state.
 *
 * @author Stefan Priebsch <stefan@priebsch.de>
 * @copyright Stefan Priebsch <stefan@priebsch.de>. All rights reserved.
 *
 * @--runTestsInSeparateProcesses
 */
class AbstractSessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var spriebsch\session\AbstractSession
     */
    protected $session;

    /**
     * @var spriebsch\session\SessionBackendInterface
     */
    protected $backend;

    /**
     * @var string
     */    
    protected $sessionName = 'a-session-name';
    
    /**
     * @var string
     */    
    protected $sessionId = 'a-session-id';

    /**
     * @var string
     */    
    protected $newSessionId = 'a-new-session-id';

    /**
     * @var string
     */    
    protected $value = 'a-value';

    /**
     * Prepares the test fixture.
     *
     * @return NULL
     */
    protected function setUp()
    {
        $this->backend = $this->getMock('spriebsch\\session\\SessionBackendInterface');
        $this->session = new ConcreteSession($this->backend);
    }
    
    /**
     * Destroys the test fixture.
     *
     * @return NULL
     */
    protected function tearDown()
    {
        unset($this->session);
        unset($this->backend);
    }

    /**
     * Dummy test to achieve constructor coverage
     *
     * @covers spriebsch\session\AbstractSession::__construct
     */
    public function testConstructorWorks()
    {
        $this->assertInstanceOf('spriebsch\\session\\tests\\stubs\\ConcreteSession', $this->session);
    }

    /**
     * Makes sure that session is not started by default
     *
     * @covers spriebsch\session\AbstractSession::isStarted
     */
    public function testIsStartedInitiallyReturnsFalse()
    {
        $this->assertFalse($this->session->isStarted());
    }

    /**
     * Makes sure that an exception is thrown when getting data is not set
     *
     * @covers spriebsch\session\AbstractSession::get
     * @expectedException spriebsch\session\SessionException
     */
    public function testGetThrowsExceptionWhenKeyDoesNotExist()
    {
        $this->session->getFoo();
    }

    /**
     * Makes sure that the set() and get() accessors work
     *
     * @covers spriebsch\session\AbstractSession::set
     * @covers spriebsch\session\AbstractSession::get
     */
    public function testSetAndGetWork()
    {
        $this->session->setFoo($this->value);
        $this->assertEquals($this->value, $this->session->getFoo());
    }

    /**
     * Makes sure that has() returns FALSE when requested data is not set
     *
     * @covers spriebsch\session\AbstractSession::has
     */
    public function testHasReturnsFalseWhenKeyDoesNotExist()
    {
        $this->assertFalse($this->session->hasFoo());
    }

    /**
     * Makes sure that start() starts a session in the backend
     *
     * @covers spriebsch\session\AbstractSession::start
     */
    public function testStartStartsSessionInBackend()
    {
        $this->backend->expects($this->once())
                      ->method('startSession')
                      ->with($this->sessionName);
    
        $this->session->start($this->sessionName);
        
        return $this->session;
    }

    /**
     * Makes sure that an exception is thrown when starting a session with empty name
     *
     * @covers spriebsch\session\AbstractSession::start
     * @covers spriebsch\session\AbstractSession::setName
     * @expectedException spriebsch\session\SessionException
     */
    public function testStartThrowsExceptionWhenNameIsEmpty()
    {
        $this->session->start('');
    }

    /**
     * Makes sure that exception is thrown by start() when session is already started
     *
     * @covers spriebsch\session\AbstractSession::start
     * @covers spriebsch\session\AbstractSession::setName
     * @depends testStartStartsSessionInBackend
     * @expectedException spriebsch\session\SessionException
     */
    public function testStartThrowsExceptionWhenSessionIsAlreadyStarted(ConcreteSession $session)
    {
        $session->start($this->sessionName);
    }

    /**
     * Starts a session and make sure that session data is read from the backend
     *
     * @covers spriebsch\session\AbstractSession::start
     * @covers spriebsch\session\AbstractSession::setName
     */
    public function testStartReadsDataFromBackend()
    {
        $this->backend->expects($this->once())
                      ->method('read')
                      ->with()
                      ->will($this->returnValue(array('foo' => $this->value)));

        $this->session->start($this->sessionName);

        $this->assertEquals($this->value, $this->session->getFoo());
    }

    /**
     * Makes sure that isStarted() returns TRUE when the session is started
     *
     * @covers spriebsch\session\AbstractSession::isStarted
     * @depends testStartStartsSessionInBackend
     */
    public function testIsStartedReturnsTrueWhenSessionIsStarted(ConcreteSession $session)
    {
        $this->assertTrue($session->isStarted());
    }

    /**
     * Makes sure that an exception is thrown by getId() when the session
     * has not been started
     *
     * @covers spriebsch\session\AbstractSession::getId
     * @covers spriebsch\session\AbstractSession::ensureSessionIsStarted
     * @expectedException spriebsch\session\SessionException
     */
    public function testGetIdThrowsExceptionWhenSessionIsNotStarted()
    {
        $this->session->getId();
    }

    /**
     * Makes sure getId() retrieves the session ID from the backend by calling
     * getSessionId().
     *
     * @covers spriebsch\session\AbstractSession::getId
     */
    public function testGetIdRetrievesSessionIdFromBackend()
    {
        $this->backend->expects($this->once())
                      ->method('getSessionId')
                      ->will($this->returnValue($this->sessionId));

        $this->session->start($this->sessionName);

        $this->assertEquals($this->sessionId, $this->session->getId());
    }

    /**
     * @covers spriebsch\session\AbstractSession::getName
     */
    public function testGetNameReturnsSessionName()
    {
        $this->session->start($this->sessionName);

        $this->assertEquals($this->sessionName, $this->session->getName());
    }

    /**
     * @covers spriebsch\session\AbstractSession::regenerateId
     * @covers spriebsch\session\AbstractSession::ensureSessionIsStarted
     * @expectedException spriebsch\session\SessionException
     */
    public function testRegenerateIdThrowsExceptionWhenSessionIsNotStarted()
    {
        $this->session->regenerateId();
    }

    /**
     * @covers spriebsch\session\AbstractSession::regenerateId
     * @covers spriebsch\session\AbstractSession::ensureSessionIsStarted
     */
    public function testRegenerateIdRegeneratesSessionIdInBackend()
    {
        $this->backend->expects($this->once())
                      ->method('regenerateSessionId');

        $this->session->start($this->sessionName);
        $this->session->regenerateId();
        
        return $this->session;
    }

    /**
     * @covers spriebsch\session\AbstractSession::regenerateId
     * @covers spriebsch\session\AbstractSession::ensureSessionIsStarted
     * @covers spriebsch\session\AbstractSession::getId
     */
    public function testSessionIdChangesWhenIdIsRegenerated()
    {
        $this->backend->expects($this->once())
                      ->method('regenerateSessionId');

        $this->backend->expects($this->once())
                      ->method('getSessionId')
                      ->will($this->returnValue($this->newSessionId));

        $this->session->start($this->sessionName);
        
        $this->assertEquals($this->newSessionId, $this->session->regenerateId());
    }

    /**
     * @covers spriebsch\session\AbstractSession::commit
     * @expectedException spriebsch\session\SessionException
     */
    public function testCommitThrowsExceptionWhenSessionIsNotStarted()
    {
        $this->session->commit();
    }
    
    /**
     * @covers spriebsch\session\AbstractSession::commit
     */
    public function testCommitWritesSessionData()
    {
        $this->session->start($this->sessionName); 
        
        $this->session->setFoo('a-value');    

        $this->backend->expects($this->once())
                      ->method('write')
                      ->with(array('foo' => 'a-value'));
           
        $this->session->commit();
    }

    /**
     * @covers spriebsch\session\AbstractSession::destroy
     * @expectedException spriebsch\session\SessionException
     */
    public function testDestroyThrowsExceptionWhenSessionIsNotStarted()
    {
        $this->session->destroy();
    }
    
    /**
     * @covers spriebsch\session\AbstractSession::destroy
     */
    public function testDestroyCallsDestroyInBackend()
    {
        $this->session->start($this->sessionName); 
        
        $this->session->setFoo('a-value');    

        $this->backend->expects($this->once())
                      ->method('destroy');
           
        $this->session->destroy();
    }
}
