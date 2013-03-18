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
 * @copyright © 2012-2013 SZen.in
 * @license   GNU General Public License, version 3
 */

namespace Tox\Web\Response;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../../src/tox/core/assembly.php';
require_once __DIR__ . '/../../../../../src/tox/application/ioutput.php';
require_once __DIR__ . '/../../../../../src/tox/application/output/output.php';
require_once __DIR__ . '/../../../../../src/tox/web/iresponse.php';
require_once __DIR__ . '/../../../../../src/tox/web/response/response.php';

require_once __DIR__ . '/../../../../../src/tox/core/exception.php';
require_once __DIR__ . '/../../../../../src/tox/application/output/@exception/closedoutput.php';
require_once __DIR__ . '/../../../../../src/tox/web/response/@exception/headersreadonly.php';
require_once __DIR__ . '/../../../../../src/tox/web/response/@exception/illegalhttpstatuscode.php';

require_once __DIR__ . '/../../../../../src/tox/application/ioutputtask.php';
require_once __DIR__ . '/../../../../../src/tox/web/ihttpheadersprocessor.php';
require_once __DIR__ . '/../../../../../src/tox/web/response/httpheadersprocessor.php';
require_once __DIR__ . '/../../../../../src/tox/web/ipagecacheagent.php';
require_once __DIR__ . '/../../../../../src/tox/web/response/pagecacheagent.php';
require_once __DIR__ . '/../../../../../src/tox/application/iview.php';
require_once __DIR__ . '/../../../../../src/tox/application/view/view.php';
require_once __DIR__ . '/../../../../../src/tox/application/istreamingview.php';
require_once __DIR__ . '/../../../../../src/tox/application/view/streamingview.php';
require_once __DIR__ . '/../../../../../src/tox/web/ipagecache.php';

use Exception as PHPException;

use Tox;

/**
 * Tests Tox\Web\Response\Response.
 *
 * @internal
 *
 * @package tox.web.response
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ResponseTest extends PHPUnit_Framework_TestCase
{
    public function testAddingHeaders()
    {
        $o_out = new Response;
        $this->assertSame($o_out, $o_out->addHeader('foo', microtime()));
        $o_out->addHeader('foo', 'bar')->addHeader('bar', 'blah.1')->addHeader('bar', 'blah.2', false);
        $this->assertEquals(array('foo' => array('bar'), 'bar' => array('blah.1', 'blah.2')), $o_out->getHeaders());
    }

    public function testHeadersSentOnOutputtingButNotBuffering()
    {
        $o_out = new Response;
        $o_hhp = $this->getMock('Tox\\Web\\IHTTPHeadersProcessor');
        $o_hhp->expects($this->never())->method('preOutput');
        $this->assertSame($o_out, $o_out->setHeadersProcessor($o_hhp));
        $o_out = new Response;
        $o_hhp = $this->getMock('Tox\\Web\\IHTTPHeadersProcessor');
        $o_hhp->expects($this->once())->method('preOutput');
        ob_start();
        $o_out->setHeadersProcessor($o_hhp)->close();
        ob_end_clean();
    }

    /**
     * @depends testHeadersSentOnOutputtingButNotBuffering
     */
    public function testHeadersSentBeforeOutputting()
    {
        $o_out = $this->getMock('Tox\\Web\\Response\\Response', array('getHeaders'));
        $o_hhp = $this->getMock('Tox\\Web\\Response\\HTTPHeadersProcessor',
                array('postOutput', 'sendHeader'),
                array($o_out)
            );
        $o_out->setHeadersProcessor($o_hhp)
            ->expects($this->once())->method('getHeaders')->will($this->returnValue(array()));
        ob_start();
        $o_out->close();
        ob_end_clean();
    }

    /**
     * @depends testHeadersSentBeforeOutputting
     * @expectedException Tox\Web\Response\HeadersReadonlyException
     */
    public function testHeadersReadonly()
    {
        $o_out = new Response;
        $o_out->setHeaders(array());
    }

    public function testPageCacheSetAfterOutputting()
    {
        $o_out = new Response;
        $o_pca = $this->getMock('Tox\\Web\\Response\\PageCacheAgent', array('preOutput'), array($o_out));
        $this->assertSame($o_out, $o_out->setPageCacheAgent($o_pca));
        $o_pc = $this->getMock('Tox\\Web\\IPageCache');
        $o_pc->expects($this->once())->method('put');
        $this->assertSame($o_out, $o_out->cacheTo($o_pc));
        ob_start();
        $o_out->close();
        ob_end_clean();
    }

    /**
     * @depends testPageCacheSetAfterOutputting
     */
    public function testGivenPageCacheWouldBeLostOnChangineAgent()
    {
        $o_out = new Response;
        $o_pc = $this->getMock('Tox\\Web\\IPageCache');
        $o_pc->expects($this->never())->method('put');
        ob_start();
        $o_out->cacheTo($o_pc)
            ->setPageCacheAgent($this->getMock('Tox\\Web\\Response\\PageCacheAgent', array(), array($o_out)))
            ->close();
        ob_end_clean();
    }

    /**
     * @depends testPageCacheSetAfterOutputting
     */
    public function testEmptyPageWouldBeCachedToo()
    {
        $o_out = new Response;
        $o_pc = $this->getMock('Tox\\Web\\IPageCache');
        $o_pc->expects($this->once())->method('put')->with($this->equalTo(''));
        ob_start();
        $o_out->cacheTo($o_pc)->close();
        ob_end_clean();
    }

    public function test301Redirecting()
    {
        $s_url = microtime();
        $o_out = $this->getMock('Tox\\Web\\Response\\Response', array('addHeader'));
        $o_out->expects($this->at(0))->method('addHeader')
            ->with($this->equalTo('Status'), $this->equalTo('301 Moved Permanently'))
            ->will($this->returnSelf());
        $o_out->expects($this->at(1))->method('addHeader')
            ->with($this->equalTo('Location'), $this->equalTo($s_url))
            ->will($this->returnSelf());
        ob_start();
        try {
            $this->assertSame($o_out, $o_out->redirect($s_url, true));
            $o_out->write('foo');
        } catch (Tox\Application\Output\ClosedOutputException $ex) {
        } catch (PHPException $ex) {
            $this->fail();
        }
        ob_end_clean();
    }

    /**
     * @depends test301Redirecting
     */
    public function test302Redirecting()
    {
        $s_url = microtime();
        $o_out = $this->getMock('Tox\\Web\\Response\\Response', array('addHeader'));
        $o_out->expects($this->at(0))->method('addHeader')
            ->with($this->equalTo('Status'), $this->equalTo('302 Found'))
            ->will($this->returnSelf());
        $o_out->expects($this->at(1))->method('addHeader')
            ->with($this->equalTo('Location'), $this->equalTo($s_url))
            ->will($this->returnSelf());
        ob_start();
        $o_out->redirect($s_url);
        ob_end_clean();
    }

    /**
     * @depends test301Redirecting
     */
    public function testRedirectingWouldDropAllBufferAndHeaders()
    {
        $o_out = new Response;
        $o_hhp = $this->getMock('Tox\\Web\\Response\\HTTPHeadersProcessor', array('sendHeader'), array($o_out));
        $o_hhp->expects($this->exactly(2))->method('sendHeader');
        $o_out->setHeadersProcessor($o_hhp)->write(microtime())->addHeader('foo', 'bar');
        ob_start();
        $o_out->redirect('blah');
        $this->assertEquals('', ob_get_clean());
    }

    /**
     * @dataProvider provideStatus
     */
    public function testSettingStatus($code, $title)
    {
        $o_out = $this->getMock('Tox\\Web\\Response\\Response', array('addHeader'));
        $o_out->expects($this->at(0))->method('addHeader')
            ->with($this->equalTo('Status'), $this->equalTo($title));
        $o_out->setStatus($code);
    }

    /**
     * @depends testSettingStatus
     * @expectedException Tox\Web\Response\IllegalHTTPStatusCodeException
     */
    public function testSettingIllegalStatus()
    {
        $o_out = new Response;
        $o_out->setStatus(987);
    }

    public function provideStatus()
    {
        return array(
            array('100', '100 Continue'),
            array('101', '101 Switching Protocols'),
            array('200', '200 OK'),
            array('201', '201 Created'),
            array('202', '202 Accepted'),
            array('203', '203 Non-Authoritative Information'),
            array('204', '204 No Content'),
            array('205', '205 Reset Content'),
            array('206', '206 Partial Content'),
            array('300', '300 Multiple Choices'),
            array('301', '301 Moved Permanently'),
            array('302', '302 Found'),
            array('303', '303 See Other'),
            array('304', '304 Not Modified'),
            array('305', '305 Use Proxy'),
            array('307', '307 Temporary Redirect'),
            array('400', '400 Bad Request'),
            array('401', '401 Unauthorized'),
            array('402', '402 Payment Required'),
            array('403', '403 Forbidden'),
            array('404', '404 Not Found'),
            array('405', '405 Method Not Allowed'),
            array('406', '406 Not Acceptable'),
            array('407', '407 Proxy Authentication Required'),
            array('408', '408 Request Timeout'),
            array('409', '409 Conflict'),
            array('410', '410 Gone'),
            array('411', '411 Length Required'),
            array('412', '412 Precondition Failed'),
            array('413', '413 Request Entity Too Large'),
            array('414', '414 Request-URI Too Long'),
            array('415', '415 Unsupported Media Type'),
            array('416', '416 Requested Range Not Satisfiable'),
            array('417', '417 Expectation Failed'),
            array('500', '500 Internal Server Error'),
            array('501', '501 Not Implemented'),
            array('502', '502 Bad Gateway'),
            array('503', '503 Service Unavailable'),
            array('504', '504 Gateway Timeout'),
            array('505', '505 HTTP Version Not Supported')
        );
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
