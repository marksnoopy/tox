<?php
/**
 * Defines the test case for Tox\Web\Response\PageCacheAgent.
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
require_once __DIR__ . '/../../../../../src/tox/application/ioutputtask.php';
require_once __DIR__ . '/../../../../../src/tox/web/ipagecacheagent.php';
require_once __DIR__ . '/../../../../../src/tox/web/response/pagecacheagent.php';

require_once __DIR__ . '/../../../../../src/tox/core/exception.php';
require_once __DIR__ . '/../../../../../src/tox/web/response/@exception/responserequired.php';

require_once __DIR__ . '/../../../../../src/tox/application/ioutput.php';
require_once __DIR__ . '/../../../../../src/tox/application/output/output.php';
require_once __DIR__ . '/../../../../../src/tox/web/iresponse.php';
require_once __DIR__ . '/../../../../../src/tox/web/response/response.php';
require_once __DIR__ . '/../../../../../src/tox/web/ihttpheadersprocessor.php';
require_once __DIR__ . '/../../../../../src/tox/web/response/httpheadersprocessor.php';
require_once __DIR__ . '/../../../../../src/tox/web/ipagecache.php';
require_once __DIR__ . '/../../../../../src/tox/application/iview.php';
require_once __DIR__ . '/../../../../../src/tox/application/view/view.php';
require_once __DIR__ . '/../../../../../src/tox/application/istreamingview.php';
require_once __DIR__ . '/../../../../../src/tox/application/view/streamingview.php';

/**
 * Tests Tox\Web\Response\PageCacheAgent.
 *
 * @internal
 *
 * @package tox.web.response
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class PageCacheAgentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException Tox\Web\Response\ResponseRequiredException
     */
    public function testConstructing()
    {
        new PageCacheAgent($this->getMock('Tox\\Application\\IOutput'));
    }

    public function testSettingPageCache()
    {
        $o_out = new Response;
        $o_pca = $this->getMock('Tox\\Web\\Response\\PageCacheAgent', array('attach'), array($o_out));
        $o_cache = $this->getMock('Tox\\Web\\IPageCache');
        $o_pca->expects($this->once())->method('attach')->with($this->equalTo($o_cache));
        $o_out->setPageCacheAgent($o_pca)->cacheTo($o_cache);
    }

    /**
     * @depends testSettingPageCache
     */
    public function testFetchingOutputtingBuffer()
    {
        $o_out = $this->getMock('Tox\\Web\\Response\\Response', array('getBuffer'));
        $o_out->setHeadersProcessor($this->getMock('Tox\\Web\\Response\\HTTPHeadersProcessor', array(), array($o_out)))
            ->cacheTo($this->getMock('Tox\\Web\\IPageCache'))
            ->expects($this->once())->method('getBuffer');
        ob_start();
        $o_out->writeClose('foo');
        ob_end_clean();
    }

    /**
     * @depends testFetchingOutputtingBuffer
     */
    public function testPageCacheRequired()
    {
        $o_out = $this->getMock('Tox\\Web\\Response\\Response', array('getBuffer'));
        $o_out->expects($this->never())->method('getBuffer');
        ob_start();
        $o_out->close();
        ob_end_clean();
    }

    /**
     * @depends testFetchingOutputtingBuffer
     */
    public function testFillingPageCache()
    {
        $s_buff = microtime();
        $o_out = new Response;
        $o_cache = $this->getMock('Tox\\Web\\IPageCache');
        $o_cache->expects($this->once())->method('put')->with($this->equalTo($s_buff));
        ob_start();
        $o_out->cacheTo($o_cache)->writeClose($s_buff);
        ob_end_clean();
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
