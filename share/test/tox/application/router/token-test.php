<?php
/**
 * Defines the test case for Tox\Application\Router\Token.
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

namespace Tox\Application\Router;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../../src/tox/core/assembly.php';
require_once __DIR__ . '/../../../../../src/tox/application/itoken.php';
require_once __DIR__ . '/../../../../../src/tox/application/router/token.php';

require_once __DIR__ . '/../../../../../src/tox/core/exception.php';
require_once __DIR__ . '/../../../../../src/tox/application/router/@exception/tokenoptionsalreadyassigned.php';

/**
 * Tests Tox\Application\Router\Token.
 *
 * @internal
 *
 * @package tox.application.router
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class TokenTest extends PHPUnit_Framework_TestCase
{
    public function testControllerNameAsTheFirstOptions()
    {
        $a_opts = range(1, 9);
        shuffle($a_opts);
        $o_token = new Token($a_opts);
        $this->assertEquals($a_opts[0], $o_token->getController());
    }

    public function testValuesAssignedToCorrespondingOptions()
    {
        $a_opts = $a_values = range(1, 9);
        shuffle($a_opts);
        shuffle($a_values);
        $o_token = new Token($a_opts);
        $this->assertSame($o_token, $o_token->assign($a_values));
        for ($ii = 1; $ii < 9; $ii++) {
            $this->assertEquals($a_values[$ii], $o_token[$a_opts[$ii]]);
        }
    }

    /**
     * @depends testValuesAssignedToCorrespondingOptions
     */
    public function testValuesUnassignedWouldBeFalse()
    {
        $o_token = new Token(array('foo', 'bar'));
        $o_token->assign(array(microtime()));
        $this->assertFalse($o_token['bar']);
    }

    /**
     * @depends testValuesAssignedToCorrespondingOptions
     * @expectedException Tox\Application\Router\TokenOptionsAlreadyAssignedException
     */
    public function testReAssigningForbidden()
    {
        $o_token = new Token(array('foo', 'bar'));
        $o_token->assign(array('blah'))->assign(array('blah'));
    }

    /**
     * @depends testValuesAssignedToCorrespondingOptions
     */
    public function testSolidValuesReadOnly()
    {
        $s_bar = microtime();
        $o_token = new Token(array('foo', 'bar'));
        $o_token->assign(array('', $s_bar));
        $o_token['bar'] = microtime();
        $this->assertEquals($s_bar, $o_token['bar']);
        unset($o_token['bar']);
        $this->assertEquals($s_bar, $o_token['bar']);
    }

    /**
     * @depends testValuesAssignedToCorrespondingOptions
     */
    public function testExporting()
    {
        $a_opts = $a_values = range(1, 9);
        shuffle($a_opts);
        shuffle($a_values);
        $o_token = new Token($a_opts);
        $o_token->assign($a_values);
        array_shift($a_opts);
        array_shift($a_values);
        $a_opts = array_combine($a_opts, $a_values);
        $this->assertEquals($a_opts, $o_token->export());
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
