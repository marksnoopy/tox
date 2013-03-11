<?php
/**
 * Defines the test case for Tox\Application\View\View.
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
require_once __DIR__ . '/../../../../../src/tox/application/view/iview.php';
require_once __DIR__ . '/../../../../../src/tox/application/view/view.php';

use stdClass;

/**
 * Tests Tox\Application\View\View.
 *
 * @internal
 *
 * @package tox.application.view
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideMetas
     */
    public function testArrayAccessImplementation($key, $value)
    {
        $o_view = $this->getMockForAbstractClass('Tox\\Application\\View\\View');
        $this->assertFalse(isset($o_view[$key]));
        $o_view[$key] = $value;
        $this->assertTrue(isset($o_view[$key]));
        $this->assertSame($value, $o_view[$key]);
        unset($o_view[$key]);
        $this->assertFalse(isset($o_view[$key]));
    }

    /**
     * @depends testArrayAccessImplementation
     */
    public function testMetasExporting()
    {
        $o_view = $this->getMockForAbstractClass('Tox\\Application\\View\\View');
        $a_metas = $this->provideMetas();
        foreach ($a_metas as $ii => $jj) {
            $o_view[$ii] = $jj;
        }
        $this->assertSame($a_metas, $o_view->export());
    }

    /**
     * @depends testArrayAccessImplementation
     */
    public function testMetasImporting()
    {
        $o_view = $this->getMockForAbstractClass('Tox\\Application\\View\\View');
        $s_key = md5('x' . microtime());
        $o_view[$s_key] = true;
        $a_metas = $this->provideMetas();
        $o_view->import($a_metas);
        $this->assertTrue(isset($o_view[$s_key]));
        foreach ($a_metas as $ii => $jj) {
            $this->assertSame($jj, $o_view[$ii]);
        }
    }

    /**
     * @depends testArrayAccessImplementation
     */
    public function testConstructedMetas()
    {
        $a_metas = array();
        foreach ($this->provideMetas() as $ii) {
            $a_metas[$ii[0]] = $ii[1];
        }
        $o_view = $this->getMockForAbstractClass('Tox\\Application\\View\\View', array($a_metas));
        foreach ($a_metas as $ii => $jj) {
            $this->assertSame($jj, $o_view[$ii]);
        }
    }

    public function provideMetas()
    {
        return array(
            array(md5('a' . microtime()), microtime()),
            array(md5('b' . microtime()), microtime(true)),
            array(md5('c' . microtime()), array(microtime())),
            array(md5('d' . microtime()), new stdClass)
        );
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
