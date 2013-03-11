<?php
/**
 * Defines the test case for Tox\Application\Output.
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

namespace Tox\Application;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/tox/core/assembly.php';
require_once __DIR__ . '/../../../../src/tox/application/ioutput.php';
require_once __DIR__ . '/../../../../src/tox/application/output.php';

require_once __DIR__ . '/../../../../src/tox/application/view/iview.php';
require_once __DIR__ . '/../../../../src/tox/application/view/istreamingview.php';
require_once __DIR__ . '/../../../../src/tox/application/view/view.php';
require_once __DIR__ . '/../../../../src/tox/application/view/streamingview.php';

/**
 * Tests Tox\Application\Output.
 *
 * @internal
 *
 * @package tox.application
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class OutputTest extends PHPUnit_Framework_TestCase
{
    public function testOutputingOnceWithAnyTimesWritings()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output');
        ob_start();
        $o1 = $o_out->write('foo');
        $o2 = $o_out->close();
        ob_end_clean();
        $this->assertSame($o_out, $o1);
        $this->assertSame($o_out, $o2);
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output');
        ob_start();
        $o1 = $o_out->writeClose('bar');
        ob_end_clean();
        $this->assertSame($o_out, $o1);
        $a_lobs = range(1, 9);
        shuffle($a_lobs);
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output');
        ob_start();
        foreach ($a_lobs as $ii) {
            $o_out->write($ii);
        }
        $s_out = ob_get_clean();
        $this->assertEquals('', $s_out);
        ob_start();
        $o_out->close();
        $s_out = ob_get_clean();
        $this->assertEquals(implode(PHP_EOL, $a_lobs), rtrim($s_out));
    }

    /**
     * @depends testOutputingOnceWithAnyTimesWritings
     * @expectedException Tox\Application\ClosedOutputException
     */
    public function testOutputFrozenAfterClose()
    {
        ob_start();
        $this->getMockForAbstractClass('Tox\\Application\\Output')->close()->write('foo');
        ob_end_clean();
    }

    /**
     * @depends testOutputingOnceWithAnyTimesWritings
     */
    public function testPreAndPostOutputing()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output');
        $o_out->expects($this->once())->method('preOutput');
        $o_out->expects($this->once())->method('postOutput');
        $o_out->close();
    }

    public function testWritingAgainstAnyOtherViewExceptStreamingView()
    {
        try {
            $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output');
            $o_out->view = $this->getMock('Tox\\Application\\View\\IView');
            $o_out->write('foo');
        } catch (StreamingViewExpectedException $e) {
        }
        $o_out->view = $this->getMock('Tox\\Application\\View\\IStreamingView');
        $o_out->write('bar');
    }

    /**
     * @depends testWritingAgainstAnyOtherViewExceptStreamingView
     */
    public function testOutputingWithAnyOtherView()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output');
        $o_out->view = $this->getMock('Tox\\Application\\View\\IView');
        $o_out->view->expects($this->once())->method('render');
        ob_start();
        $o_out->close();
        ob_end_clean();
    }

    public function testStreamingDisabledAtFirst()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output');
        $this->assertFalse($o_out->isStreaming());
    }

    /**
     * @depends testStreamingDisabledAtFirst
     */
    public function testEnablingAndDisablingStreaming()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output');
        $this->assertSame($o_out, $o_out->enableStreaming());
        $this->assertTrue($o_out->isStreaming());
        $this->assertSame($o_out, $o_out->disableStreaming());
        $this->assertFalse($o_out->isStreaming());
    }

    /**
     * @depends testEnablingAndDisablingStreaming
     */
    public function testStreamingModeAgainstAnyOtherViewExceptStreamingView()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output');
        $o_out->view = $this->getMock('Tox\\Application\\View\\IView');
        try {
            $o_out->enableStreaming();
        } catch (StreamingViewExpectedException $e) {
        }
        try {
            $o_out->disableStreaming();
        } catch (StreamingViewExpectedException $e) {
        }
    }

    /**
     * @depends testEnablingAndDisablingStreaming
     */
    public function testOutputingImmediatelyOnStreaming()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output')->enableStreaming();
        $a_lobs = range(1, 9);
        shuffle($a_lobs);
        foreach ($a_lobs as $ii) {
            ob_start();
            $o_out->write($ii);
            $this->assertEquals(ob_get_clean(), $ii);
        }
    }

    /**
     * @depends testOutputingImmediatelyOnStreaming
     */
    public function testPreAndPostOutputingOnStreaming()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output')->enableStreaming();
        $o_out->expects($this->extactly(9))->method('preOutput');
        $o_out->expects($this->extactly(9))->method('postOutput');
        $a_lobs = range(1, 9);
        shuffle($a_lobs);
        ob_start();
        foreach ($a_lobs as $ii) {
            $o_out->write($ii);
        }
    }}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
