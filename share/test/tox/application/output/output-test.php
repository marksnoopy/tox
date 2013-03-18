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

namespace Tox\Application\Output;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../../src/tox/core/assembly.php';
require_once __DIR__ . '/../../../../../src/tox/application/ioutput.php';
require_once __DIR__ . '/../../../../../src/tox/application/output/output.php';

require_once __DIR__ . '/../../../../../src/tox/core/exception.php';
require_once __DIR__ . '/../../../../../src/tox/application/output/@exception/closedoutput.php';
require_once __DIR__ . '/../../../../../src/tox/application/output/@exception/bufferreadonly.php';
require_once __DIR__ . '/../../../../../src/tox/application/output/@exception/streamingviewexpected.php';

require_once __DIR__ . '/../../../../../src/tox/application/iview.php';
require_once __DIR__ . '/../../../../../src/tox/application/istreamingview.php';
require_once __DIR__ . '/../../../../../src/tox/application/view/view.php';
require_once __DIR__ . '/../../../../../src/tox/application/view/streamingview.php';
require_once __DIR__ . '/../../../../../src/tox/application/ioutputtask.php';

use Exception as PHPException;

use Tox\Application;

/**
 * Tests Tox\Application\Output.
 *
 * @internal
 *
 * @package tox.application.output
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class OutputTest extends PHPUnit_Framework_TestCase
{
    public function testOutputtingOnceWithAnyTimesWritings()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        ob_start();
        $o1 = $o_out->write('foo');
        $o2 = $o_out->close();
        ob_end_clean();
        $this->assertSame($o_out, $o1);
        $this->assertSame($o_out, $o2);
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        ob_start();
        $o1 = $o_out->writeClose('bar');
        ob_end_clean();
        $this->assertSame($o_out, $o1);
        $a_lobs = range(1, 9);
        shuffle($a_lobs);
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        ob_start();
        foreach ($a_lobs as $ii) {
            $o_out->write($ii);
        }
        $s_out = ob_get_clean();
        $this->assertEquals('', $s_out);
        ob_start();
        $o_out->close();
        $s_out = ob_get_clean();
        $this->assertEquals(implode('', $a_lobs), rtrim($s_out));
    }

    /**
     * @depends testOutputtingOnceWithAnyTimesWritings
     * @expectedException Tox\Application\Output\ClosedOutputException
     */
    public function testOutputFrozenAfterClose()
    {
        ob_start();
        $this->getMockForAbstractClass('Tox\\Application\\Output\\Output')->close()->write('foo');
        ob_end_clean();
    }

    public function testOutputtingBufferReadableForTasks()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        $s_lob = $s_buff = microtime();
        $this->assertEquals($s_lob, $o_out->write($s_lob)->getBuffer());
        $s_lob = microtime(true);
        $this->assertEquals($s_buff . $s_lob, $o_out->write($s_lob)->getBuffer());
    }

    /**
     * @expectedException Tox\Application\Output\BufferReadonlyException
     */
    public function testBufferAccessableOnOutputtingOnly()
    {
        $this->getMockForAbstractClass('Tox\\Application\\Output\\Output')->setBuffer('foo');
    }

    /**
     * @depends testOutputtingOnceWithAnyTimesWritings
     */
    public function testPreAndPostOutputting()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        $o_task1 = new TaskMock($o_out);
        $o_task1->name = microtime();
        $o_task2 = new TaskMock($o_out);
        $o_task2->name = microtime();
        $this->assertSame($o_out, $o_out->addTask($o_task1));
        TaskMock::$log = array();
        ob_start();
        $o_out->addTask($o_task2)->close();
        ob_end_clean();
        $this->assertEquals(array(
                $o_task1->name . '::preOutput()',
                $o_task2->name . '::preOutput()',
                $o_task2->name . '::postOutput()',
                $o_task1->name . '::postOutput()'
            ),
            TaskMock::$log
        );
    }

    public function testWritingAgainstAnyOtherViewExceptStreamingView()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        $o_out->setView($this->getMock('Tox\\Application\\IView'));
        try {
            $o_out->write('foo');
        } catch (StreamingViewExpectedException $ex) {
        } catch (PHPException $ex) {
            $this->fail();
        }
        $o_out->setView($this->getMock('Tox\\Application\\IStreamingView'));
        try {
            $o_out->write('foo');
        } catch (PHPException $ex) {
            $this->fail();
        }
        $this->assertTrue(true);
    }

    /**
     * @depends testWritingAgainstAnyOtherViewExceptStreamingView
     */
    public function testOutputtingWithAnyOtherView()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        $o_view = $this->getMock('Tox\\Application\\IView');
        $o_view->expects($this->once())->method('render');
        $o_out->setView($o_view);
        ob_start();
        $o_out->close();
        ob_end_clean();
    }

    public function testStreamingDisabledAtFirst()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        $this->assertFalse($o_out->isStreaming());
    }

    /**
     * @depends testStreamingDisabledAtFirst
     */
    public function testEnablingAndDisablingStreaming()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
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
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        $o_out->setView($this->getMock('Tox\\Application\\IView'));
        try {
            $o_out->enableStreaming();
        } catch (StreamingViewExpectedException $e) {
        } catch (PHPException $ex) {
            $this->fail();
        }
        try {
            $o_out->disableStreaming();
        } catch (StreamingViewExpectedException $e) {
        } catch (PHPException $ex) {
            $this->fail();
        }
        $this->assertTrue(true);
    }

    /**
     * @depends testEnablingAndDisablingStreaming
     */
    public function testOutputtingImmediatelyOnStreaming()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output')->enableStreaming();
        $a_lobs = range(1, 9);
        shuffle($a_lobs);
        foreach ($a_lobs as $ii) {
            ob_start();
            $o_out->write($ii);
            $this->assertEquals(ob_get_clean(), $ii);
        }
    }

    /**
     * @depends testOutputtingImmediatelyOnStreaming
     */
    public function testPreAndPostOutputtingOnStreaming()
    {
        $o_out = $this->getMockForAbstractClass('Tox\\Application\\Output\\Output');
        $o_task1 = new TaskMock($o_out);
        $o_task1->name = md5('foo' . microtime());
        $o_task2 = new TaskMock($o_out);
        $o_task2->name = md5('bar' . microtime());
        $o_out->addTask($o_task1)->addTask($o_task2)->enableStreaming();
        TaskMock::$log = array();
        ob_start();
        $o_out->write('foo')->write('bar');
        ob_end_clean();
        $this->assertEquals(array(
                $o_task1->name . '::preOutput()',
                $o_task2->name . '::preOutput()',
                $o_task2->name . '::postOutput()',
                $o_task1->name . '::postOutput()',
                $o_task1->name . '::preOutput()',
                $o_task2->name . '::preOutput()',
                $o_task2->name . '::postOutput()',
                $o_task1->name . '::postOutput()'
            ),
            TaskMock::$log
        );
    }
}

/**
 * Represents as a task for mocking test.
 *
 * @internal
 *
 * @package tox.application.output
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 *
 * @property mixed $ok
 */
class TaskMock implements Application\IOutputTask
{
    /**
     * Retrieves or sets the tracing logs.
     *
     * @var string[]
     */
    public static $log = array();

    /**
     * Retrieves and sets the name of the instance.
     *
     * @var string
     */
    public $name;

    /**
     * Stores the related output.
     *
     * @var Application\IOutput
     */
    protected $output;

    /**
     * {@inheritdoc}
     *
     * @param Application\IOutput $output The output which to be used for.
     */
    public function __construct(Application\IOutput $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function preOutput()
    {
        self::$log[] = $this->name . '::preOutput()';
        $this->output->setBuffer(microtime());
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function postOutput()
    {
        self::$log[] = $this->name . '::postOutput()';
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
