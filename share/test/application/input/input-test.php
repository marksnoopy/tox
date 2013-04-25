<?php
/**
 * Defines the test case for Tox\Application\Input.
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

namespace Tox\Application\Input;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/application/iinput.php';
require_once __DIR__ . '/../../../../src/application/input/input.php';

require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/type/simple/unexpectedtypeexception.php';
require_once __DIR__ . '/../../../../src/application/input/unknowntypeexception.php';

require_once __DIR__ . '/../../../../src/web/irequest.php';
require_once __DIR__ . '/../../../../src/web/request/request.php';
require_once __DIR__ . '/../../../../src/type/iboxable.php';
require_once __DIR__ . '/../../../../src/type/type.php';
require_once __DIR__ . '/../../../../src/type/simple/email.php';

use Exception as PHPException;

use Tox;

/**
 * Tests Tox\Application\Input.
 *
 * @internal
 *
 * @package tox.application.output
 * @author  Mark Snoopy <marksnoopy@gmail.com>
 */
class OutputTest extends PHPUnit_Framework_TestCase
{
    public function testDefault()
    {
        $o_input = $this->getMockBuilder('Tox\\Application\\Input\\Input')
            ->getMockForAbstractClass();
        $this->assertEquals($o_input->defaults('post.a', 'aaaa'), $o_input);
    }

    /**
     * @dataProvider provideErrorTypeData
     * @expectedException Tox\Application\Input\UnknownTypeException
     */
    public function testNotExpecetedType($type) {
        $o_input = $this->getMockBuilder('Tox\\Application\\Input\\Input')
            ->getMockForAbstractClass();
        $o_input->expected('post.email', $type);
    }

    public function provideErrorTypeData()
    {
        return array(
            array('aaaaaa'),
            array('$##@'),
        );
    }

    public function provideTypeData()
    {
        return array(
            array('email'),
        );
    }



}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
