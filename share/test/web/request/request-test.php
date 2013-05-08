<?php
/**
 * Defines the test case for Tox\Web\Response\Response.
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
 * @copyright © 2012-2013 PHP-Tox.org
 * @license   GNU General Public License, version 3
 */

namespace Tox\Web\Request;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/application/iinput.php';
require_once __DIR__ . '/../../../../src/application/input/input.php';
require_once __DIR__ . '/../../../../src/web/irequest.php';
require_once __DIR__ . '/../../../../src/web/request/request.php';
require_once __DIR__ . '/../../../../src/application/itoken.php';
require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/web/request/unknownmetaexception.php';




use Tox;

/**
 * Tests Tox\Web\Request.
 *
 * @internal
 *
 * @package tox.web.request
 * @author  Mark Snoopy <marksnoopy@gmail.com>
 */
class RequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideResetData
     */
    public function testResetVariable($key, $value)
    {
        $i_server = count($_SERVER);
        $_COOKIE[$key] = $_ENV[$key] = $_FILES[$key]
            = $_GET[$key] = $_POST[$key] = $_SERVER[$key] = $value;

        $this->assertEquals($_COOKIE, array($key=> $value));
        //$this->assertEquals($_ENV, array($key=> $value));
        $this->assertEquals($_FILES, array($key=> $value));
        $this->assertEquals($_GET, array($key=> $value));
        $this->assertEquals($_POST, array($key=> $value));
        $this->assertCount(($i_server +1), $_SERVER);

        $o_request = new Request;

        $this->assertEquals($_COOKIE, array());
        $this->assertEquals($_ENV, array());
        $this->assertEquals($_FILES, array());
        $this->assertEquals($_GET, array());
        $this->assertEquals($_POST, array());
        $this->assertEquals($_SERVER, array());
    }

    /**
     * @dataProvider provideSuccessVariableData
     * @depends testResetVariable
     */
    public function testSuccessGetVariable($variable_type, $key, $value)
    {
        switch ($variable_type) {
            case 'cookie':
                $_COOKIE[$key] = $value;
                break;
            case 'env':
                $_ENV[$key] = $value;
                break;
            case 'get':
                $_GET[$key] = $value;
                break;
            case 'post':
                $_POST[$key] = $value;
                break;
            case 'server':
                $_SERVER[$key] = $value;
                break;
        }
        $o_request = new Request;
        $this->assertEquals($value, $o_request[$variable_type. '.' .$key]);

    }

    /**
     * @dataProvider provideErrorVariableData
     * @expectedException Tox\Web\Request\UnknownMetaException
     */
    public function testErrorGetVariable($key)
    {
        $o_request = new Request;
        $o_request[$key];
    }
    /**
     * @dataProvider provideErrorVariableData
     *
     */
    public function testDefaultBeforeGetVariable($key)
    {
        $o_request = new Request;
        $s_value = md5(microtime());
        $o_request->defaults($key, $s_value);
        $this->assertEquals($s_value, $o_request[$key]);
    }

    /**
     * @dataProvider provideSuccessVariableData
     */
    public function testExistVariable($variable_type, $key, $value)
    {
        switch ($variable_type) {
            case 'cookie':
                $_COOKIE[$key] = $value;
                break;
            case 'env':
                $_ENV[$key] = $value;
                break;
            case 'get':
                $_GET[$key] = $value;
                break;
            case 'post':
                $_POST[$key] = $value;
                break;
            case 'server':
                $_SERVER[$key] = $value;
                break;
        }
        $o_request = new Request;
        $this->assertTrue(isset($o_request[$variable_type. '.' .$key]));
    }

    /**
     * @depends testResetVariable
     */
    public function testRoute()
    {
        $a_server = $_SERVER;
        $o_token = $this->getMockForAbstractClass('Tox\\Application\\IToken');
        $o_token->expects($this->once())->method('export');
        $o_request = new Request;
        $o_request->recruit($o_token);
        foreach ($a_server as $k => $v) {
            $this->assertEquals($a_server[$k], $o_request['server.' .strtolower($k)]);
        }

    }

    /**
     * @depends testResetVariable
     */
    public function testRawurldecode()
    {
        $_SERVER['request_uri'] = '你好';
        $o_request = new Request;
        $this->assertEquals(rawurldecode('你好'), $o_request->getCommandLine());
    }

    /**
     * @dataProvider provideErrorVariableData
     */
    public function testNotExistVariable($key)
    {
        $o_request = new Request;
        $this->assertFalse(isset($o_request[$key]));
    }

    /**
     * @dataProvider provideSuccessVariableData
     */
    public function testUnsetVariable($variable_type, $key, $value)
    {
        switch ($variable_type) {
            case 'cookie':
                $_COOKIE[$key] = $value;
                break;
            case 'env':
                $_ENV[$key] = $value;
                break;
            case 'get':
                $_GET[$key] = $value;
                break;
            case 'post':
                $_POST[$key] = $value;
                break;
            case 'server':
                $_SERVER[$key] = $value;
                break;
        }
        $o_request = new Request;
        $o_request->offsetUnset($o_request[$variable_type. '.' .$key]);
        $this->assertEquals($value, $o_request[$variable_type. '.' .$key]);
    }

    /**
     * @dataProvider provideResetData
     * @expectedException Tox\Web\Request\UnknownMetaException
     */
    public function testSetVariable($key, $value)
    {
        $o_request = new Request;
        $o_request->offsetSet($key, $value);
        $o_request[$key];
    }

    public function provideResetData()
    {
        return array(
            array('testkey', 'testvalue'),
        );
    }

    public function provideSuccessVariableData()
    {
        return array(
            array('cookie', rand(1, 9), rand(1, 9)),
            array('env',  rand(1, 9), rand(1, 9)),
            array('get',  rand(1, 9), rand(1, 9)),
            array('post',  rand(1, 9), rand(1, 9)),
            array('server',  rand(1, 9), rand(1, 9)),
        );
    }

    public function provideErrorVariableData()
    {
        return array(
            array('cookies.errorkey'),
            array('env.error'),
            array('get.error'),
            array('post.error'),
            array('server.error'),
        );
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
