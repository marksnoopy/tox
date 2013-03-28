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
require_once __DIR__ . '/../../../../../src/tox/application/irouter.php';
require_once __DIR__ . '/../../../../../src/tox/application/router/router.php';

require_once __DIR__ . '/../../../../../src/tox/core/exception.php';
require_once __DIR__ . '/../../../../../src/tox/application/router/@exception/unknownapplicationsituation.php';

require_once __DIR__ . '/../../../../../src/tox/application/iinput.php';
require_once __DIR__ . '/../../../../../src/tox/application/itoken.php';
require_once __DIR__ . '/../../../../../src/tox/application/router/token.php';

/**
 * Tests Tox\Application\Router\Token.
 *
 * @internal
 *
 * @package tox.application.router
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testRouting()
    {
        $o_in = $this->getMock('Tox\\Application\\IInput');
        $o_in->expects($this->once())->method('getCommandLine')->will($this->returnValue('--help -- a b'));
        $o_router = new Router(array('--help -- (.) (.)' => array('foo', 'bar', 'blah')));
        $o_token = $o_router->analyse($o_in);
        $this->assertEquals('foo', $o_token->getController());
        $this->assertEquals('a', $o_token['bar']);
        $this->assertEquals('b', $o_token['blah']);
    }

    /**
     * @depends testRouting
     * @expectedException Tox\Application\Router\UnknownApplicationSituationException
     */
    public function testNoRuleMatched()
    {
        $o_in = $this->getMock('Tox\\Application\\IInput');
        $o_in->expects($this->once())->method('getCommandLine')->will($this->returnValue('--help -- a b'));
        $o_router = new Router;
        $o_token = $o_router->analyse($o_in);
    }

    /**
     * @depends testRouting
     */
    public function testRulesPrepending()
    {
        $o_in = $this->getMock('Tox\\Application\\IInput');
        $o_in->expects($this->once())->method('getCommandLine')->will($this->returnValue('--help -- a b'));
        $o_router = new Router(array('--help -- (.) (.)' => array('foo', 'bar', 'blah')));
        $o_token = $o_router->import(array('--help -- (.*) (.*)' => array('foo2', 'bar2', 'blah2')), true)
            ->analyse($o_in);
        $this->assertEquals('foo2', $o_token->getController());
        $this->assertEquals('a', $o_token['bar2']);
        $this->assertEquals('b', $o_token['blah2']);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
