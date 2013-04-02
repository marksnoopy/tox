<?php
/**
 * Defines the test case for Tox\Application\View\StreamingView.
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

namespace Tox\Application\View;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/application/iview.php';
require_once __DIR__ . '/../../../../src/application/view/view.php';
require_once __DIR__ . '/../../../../src/application/istreamingview.php';
require_once __DIR__ . '/../../../../src/application/view/streamingview.php';

require_once __DIR__ . '/../../../../src/application/ioutput.php';
require_once __DIR__ . '/../../../../src/application/ioutputtask.php';

use Tox\Application\IOutput;

/**
 * Tests Tox\Application\View\StreamingView.
 *
 * @internal
 *
 * @package tox.application.view
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class StreamingViewTest extends PHPUnit_Framework_TestCase
{
    public function testStreamingWithSpecialBuffer()
    {
        $o_out = $this->getMock('Tox\\Application\\IOutput');
        $o_stream = new StreamingView($o_out);
        $o_stream['foo'] = 'bar';
        $this->assertEquals('', $o_stream->render());
    }

    public function testDefaultRenderingBehavior()
    {
        $a_blobs = range(1, 99);
        shuffle($a_blobs);
        $o_stream = new StreamingView($this->getMock('Tox\\Application\\IOutput'));
        $s_blob = implode('', $a_blobs);
        foreach ($a_blobs as $ii) {
            $o_stream->append($ii);
        }
        $this->assertEquals($s_blob, $o_stream->render());
    }

    public function testNotifyOutputOnStreaming()
    {
        $i_times = rand(1, 10);
        $o_out = $this->getMock('Tox\\Application\\IOutput');
        $o_out->expects($this->exactly($i_times))->method('notifyStreaming');
        $o_stream = new StreamingView($o_out);
        for ($ii = 0; $ii < $i_times; $ii ++) {
            $o_stream->append(microtime());
        }
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
