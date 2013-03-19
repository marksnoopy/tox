<?php
/**
 * Defines the test case for Tox\Application\View\Fallback.
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

require_once __DIR__ . '/../../../../../src/tox/core/assembly.php';
require_once __DIR__ . '/../../../../../src/tox/application/iview.php';
require_once __DIR__ . '/../../../../../src/tox/application/view/view.php';
require_once __DIR__ . '/../../../../../src/tox/application/ifallback.php';
require_once __DIR__ . '/../../../../../src/tox/application/view/fallback.php';

use Exception;

/**
 * Tests Tox\Application\View\Fallback.
 *
 * @internal
 *
 * @package tox.application.view
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class FallbackTest extends PHPUnit_Framework_TestCase
{
    public function testCausingOfAnyException()
    {
        $o_fb = new Fallback;
        $this->assertSame($o_fb, $o_fb->cause(new Exception));
        echo($o_fb->render());
    }

    /**
     * @depends testCausingOfAnyException
     */
    public function testEachFallbackLivesWithOnlyOneException()
    {
        $o_fb = new Fallback;
        $this->assertEquals($o_fb->cause(new Exception(microtime()))->render(),
            $o_fb->cause(new Exception(microtime()))->render()
        );
    }

    public function testRenderingGotDoneOnCausing()
    {
        $o_fb = $this->getMock('Tox\\Application\\View\\Fallback', array('render'));
        $o_fb->expects($this->once())->method('render');
        $o_fb->cause(new Exception);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
