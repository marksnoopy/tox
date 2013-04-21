<?php
/**
 * Defines the test case for Tox\Web\Response\HTTPHeadersProcessor.
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

namespace Tox\Web\Response;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/application/ioutputtask.php';
require_once __DIR__ . '/../../../../src/web/ihttpheadersprocessor.php';
require_once __DIR__ . '/../../../../src/web/response/httpheadersprocessor.php';

require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/web/response/responserequiredexception.php';

require_once __DIR__ . '/../../../../src/application/ioutput.php';
require_once __DIR__ . '/../../../../src/application/output/output.php';
require_once __DIR__ . '/../../../../src/web/iresponse.php';
require_once __DIR__ . '/../../../../src/web/response/response.php';
require_once __DIR__ . '/../../../../src/web/ipagecacheagent.php';
require_once __DIR__ . '/../../../../src/web/response/pagecacheagent.php';
require_once __DIR__ . '/../../../../src/application/iview.php';
require_once __DIR__ . '/../../../../src/application/view/view.php';
require_once __DIR__ . '/../../../../src/application/istreamingview.php';
require_once __DIR__ . '/../../../../src/application/view/streamingview.php';

use Exception;
use PHPUnit_Framework_Error;

/**
 * Tests Tox\Web\Response\HTTPHeadersProcessor.
 *
 * @internal
 *
 * @package tox.web.response
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class HTTPHeadersProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Tox\Web\Response\ResponseRequiredException
     */
    public function testConstructing()
    {
        new HTTPHeadersProcessor($this->getMock('Tox\\Application\\IOutput'));
    }

    public function testFetchingHeaders()
    {
        $o_out = $this->getMock('Tox\\Web\\Response\\Response', array('getHeaders'));
        $o_out->expects($this->once())->method('getHeaders')->will($this->returnValue(array()));
        $o_hhp = new HTTPHeadersProcessor($o_out);
        $o_hhp->preOutput();
    }

    /**
     * @depends testFetchingHeaders
     */
    public function testSendingParsedHeaders()
    {
        $o_out = new Response;
        $o_hhp = $this->getMock('Tox\\Web\\Response\\HTTPHeadersProcessor', array('sendHeader'), array($o_out));
        $o_hhp->expects($this->at(0))->method('sendHeader')
            ->with($this->equalTo('Date'), $this->equalTo('foo'));
        $o_hhp->expects($this->at(1))->method('sendHeader')
            ->with($this->equalTo('Date'), $this->equalTo('bar'));
        $o_out->addHeader('Date', microtime())->addHeader('Date', 'foo')->addHeader('Date', 'bar', false)
            ->setHeadersProcessor($o_hhp);
        ob_start();
        $o_out->close();
        ob_end_clean();
    }

    /**
     * @depends testSendingParsedHeaders
     */
    public function testCleaningSentHeaders()
    {
        $o_out = $this->getMock('Tox\\Web\\Response\\Response', array('setHeaders'));
        $o_out->expects($this->never())->method('setHeaders');
        ob_start();
        $o_out->close();
        ob_end_clean();
        $o_out = $this->getMock('Tox\\Web\\Response\\Response', array('setHeaders'));
        $o_hhp = $this->getMock('Tox\\Web\\Response\\HTTPHeadersProcessor', array('sendHeader'), array($o_out));
        $o_out->setHeadersProcessor($o_hhp)->addHeader('Date', microtime())
            ->expects($this->once())->method('setHeaders')->with($this->equalTo(array()));
        ob_start();
        $o_out->close();
        ob_end_clean();
    }

    /**
     * @depends testSendingParsedHeaders
     */
    public function testHeadersReallySent()
    {
        $m_value = microtime();
        $o_out = new Response;
        $o_out->addHeader('X-Foo', $m_value);
        try {
            ob_start();
            $o_out->close();
            ob_end_clean();
            $this->assertEquals(array('X-Foo: ' . $m_value), xdebug_get_headers());
        } catch (PHPUnit_Framework_Error $ex) {
            // XXX Uses `Tox\Core\ClassManager' to recognise whether in
            // `processIsolation' mode.
            $this->assertTrue(class_exists('Tox\\Core\\ClassManager'));
        } catch (Exception $ex) {
            $this->fail();
        }
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
