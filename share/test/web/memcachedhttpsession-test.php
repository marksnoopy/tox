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

require_once __DIR__ . '/../../../src/core/assembly.php';
require_once __DIR__ . '/../../../src/core/exception.php';
require_once __DIR__ . '/../../../src/web/@exception/sessionsavepathnotvaild.php';
require_once __DIR__ . '/../../../src/web/@exception/sessionalreadystart.php';


require_once __DIR__ . '/../../../src/data/isource.php';
require_once __DIR__ . '/../../../src/data/ikv.php';
require_once __DIR__ . '/../../../src/data/kv/kv.php';
require_once __DIR__ . '/../../../src/data/kv/memcacheserverconfiguration.php';
require_once __DIR__ . '/../../../src/data/kv/@exception/memcachevaluenotstring.php';
require_once __DIR__ . '/../../../src/data/kv/@exception/memcachekeytoolong.php';
require_once __DIR__ . '/../../../src/data/kv/@exception/emptyhost.php';
require_once __DIR__ . '/../../../src/data/kv/@exception/memcacheconfignotarray.php';
require_once __DIR__ . '/../../../src/data/kv/memcache.php';



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
class MemcachedHttpSessionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        if (session_id() !== '') {
            session_destroy();
            session_write_close();
        }
    }

    /**
     * @dataProvider dataProviderMemcachedConfig
     */
    public function testNotExitInHypervariableWhenSet($config)
    {

        $o_session = new Tox\Web\MemcachedHttpSession();
        $o_session->init($config);
        $this->assertEmpty($o_session->Export());
        $o_session->setSession('foo', 'value');
        $this->assertFalse(isset($_SESSION['foo']));
        $_SESSION['test1'] = 'tt';
        $this->assertEmpty($o_session->getSession('test1'));
    }

    /**
     * @dataProvider dataProviderMemcachedConfig
     */
    public function testWriteSession($config)
    {
        $mockmem = $this->getMockBuilder('Tox\\Data\\Kv\\Memcache')
                ->setMethods(array('set', 'init', 'setServer'))
                ->getMock();

        $mockmem->Expects($this->once())
                ->method('set')
                ->will($this->returnValue(true));

        $mocksession = $this->getMockBuilder('Tox\\Web\\MemcachedHttpSession')
                ->setMethods(array('newMemcache','useMemcachedStoreSession'))
                ->getMockForAbstractClass();

        $mocksession->Expects($this->once())
                ->method('newMemcache')
                ->will($this->returnValue($mockmem));

        $mocksession->Expects($this->once())
                ->method('useMemcachedStoreSession')
                ->will($this->returnValue(false));

        $mocksession->init($config);

        $mocksession->writeSession(session_id(), array('t' => 'tt'));

    }

    public function dataProviderMemcachedConfig()
    {
        return array(array(
                $a_array = array(
            'useMemcached' => true,
            'memcached' => array(
                array(
                    'host' => '127.0.0.1',
                    'port' => '11211',
                ),
            )
                )
            )
        );
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
