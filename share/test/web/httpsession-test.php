<?php
/**
 * Defines the test case for Tox\Web\HttpSession.
 *
 * This file is part of Tox.
 *
 * Tox is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Tox is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tox.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @copyright © 2012-2013 SZen.in
 * @license   GNU General Public License, version 3
 */

namespace Tox\Web;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../src/core/exception.php';
require_once __DIR__ . '/../../../src/web/@exception/sessionsavepathnotvaild.php';
require_once __DIR__ . '/../../../src/web/@exception/sessionalreadystart.php';

require_once __DIR__ . '/../../../src/core/assembly.php';
require_once __DIR__ . '/../../../src/web/ihttpsession.php';
require_once __DIR__ . '/../../../src/web/ihttpsession.php';
require_once __DIR__ . '/../../../src/web/httpsession.php';
require_once __DIR__ . '/../../../src/web/memcachedhttpsession.php';

use Tox\Web;
use Tox;

/**
 * Tests Tox\Data\KV.
 *
 * @internal
 *
 * @package tox.data.kv
 * @author  Qiang Fu <fuqiang007enter@gmail.com>
 */
class HttpSessionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        if (session_id() !== '') {
            session_destroy();
            session_write_close();
        }
    }

    public function testNotExitInHypervariableWhenSet()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $this->assertEmpty($o_session->Export());
        $o_session->setSession('foo', 'value');
        $this->assertFalse(isset($_SESSION['foo']));
        $_SESSION['test1'] = 'tt';
        $this->assertEmpty($o_session->getSession('test1'));
    }

    public function testSetAndGetSessionSavePath()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->setSavePath('/home/fu/www/log');
        $o_session->init();
        $o_session->setSession('foo', 'value');
        $this->assertFalse(isset($_SESSION['foo']));
        $this->assertEquals('/home/fu/www/log', $o_session->getSavePath());
    }

    public function testSetAndGetSessionTimeout()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->setTimeout(500);
        $o_session->init();
        $this->assertEquals(500, $o_session->getTimeout());
    }

    public function testSessionClose()
    {
        $mockememcachedSession = $this->getMockBuilder('Tox\\Web\\HttpSession')
                ->setMethods(array('useMemcachedStoreSession'))
                ->getMock();
        $mockememcachedSession->Expects($this->any())
                ->method('useMemcachedStoreSession')
                ->will($this->returnValue(true));
        $mockememcachedSession->init();
    }

    /**
     * @expectedException Tox\Web\SessionSavePathNotVaildException
     */
    public function testSetSavePathException()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->setSavePath('ttt');
    }

    /**
     * @expectedException Tox\Web\SessionAlreadyStartException
     */
    public function testSessionAlreadyStartException()
    {
        session_start();
        $o_session = new Tox\Web\HttpSession();
    }

    public function testSetCookieParamsValueIsTrue()
    {
        $mockSession = $this->getMockBuilder('session')
                ->setMethods(array('session_set_cookie_params'))
                ->getMock();
        $mockSession->Expects($this->any())
                ->method('session_set_cookie_params');

        $arrayParams = array(
            'lifetime' => 500,
            'path' => '\test',
            'domain' => '',
            'secure' => false,
        );
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $o_session->setCookieParams($arrayParams);

        $arrayTemp = $o_session->getCookieParams();
        $this->assertEquals(500, $arrayTemp['lifetime']);
    }

    public function testGetSessionID()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $t = $o_session->getSessionID();
        $this->assertNotEmpty($t);

        $o_session->destroy();
        $this->assertEmpty($o_session->getSessionID());
    }

    public function testGetSessionAfterSet()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $o_session->setSession('foo', 'value');
        $this->assertEquals('value', $o_session->getSession('foo'));
    }

    public function testEmptyAfterDestory()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $o_session->setSession('foo', 'value');
        $o_session->destroy();
        $this->assertEquals(null, $o_session->getSessionID());
    }

    public function testEmptyAfterRemoveFromSession()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $o_session->setSession('foo', 'value');
        $this->assertEquals('value', $o_session->getSession('foo'));

        $this->assertEquals('value', $o_session->removeSession('foo'));

        $this->assertEquals(null, $o_session->getSession('foo'));
        $this->assertEquals(null, $o_session->removeSession('foosss'));
    }

    public function testEmptyAfterClearSession()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $o_session->setSession('foo', 'value');

        $o_session->clearSession();

        $this->assertEquals(null, $o_session->getSession('foo'));
    }

    public function testSessionHanderFunction()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $this->assertTrue($o_session->openSession('/tmp', session_name()));
        $this->assertTrue($o_session->closeSession());
        $this->assertEquals('', $o_session->readSession(session_id()));
        $this->assertTrue($o_session->writeSession(session_id(), array()));
        $this->assertTrue($o_session->destroySession(session_id()));
        $this->assertTrue($o_session->gcSession(500));
    }

    public function testExportSession()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $o_session->setSession('foo', 'value');

        $this->assertArrayHasKey('foo', $o_session->Export());
    }

    public function testOffSetAndOffGet()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $o_session['foo'] = 'val';
        $this->assertEquals('val', $o_session['foo']);
    }

    public function testOffSetExists()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $o_session['foo'] = 'val';
        $this->assertTrue(isset($o_session['foo']));
    }

    public function testOffSetUnset()
    {
        $o_session = new Tox\Web\HttpSession();
        $o_session->init();
        $o_session['foo'] = 'val';
        unset($o_session['foo']);
        $this->assertEquals(null, $o_session['foo']);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
