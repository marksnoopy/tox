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
 * @copyright © 2012-2013 PHP-Tox.org
 * @license   GNU General Public License, version 3
 */

namespace Tox\Type\Simple;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';;
require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/type/simple/unexpectedtypeexception.php';
require_once __DIR__ . '/../../../../src/type/iboxable.php';
require_once __DIR__ . '/../../../../src/type/type.php';
require_once __DIR__ . '/../../../../src/type/simple/email.php';

use Tox;

/**
 * Tests Tox\Type\Simple\Email.
 *
 * @internal
 *
 * @package tox.type.simple.email
 * @author  Mark Snoopy <marksnoopy@gmail.com>
 */
class EmailTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideErrorData
     * @expectedException Tox\Type\Simple\UnexpectedTypeException
     */
    public function testErrorEmail($value) {
        new Tox\Type\Simple\Email($value);
    }

    /**
     * @dataProvider provideReturnData
     */
    public function testReturnValue($value) {
        $o_email = new Tox\Type\Simple\Email($value);
        $this->assertEquals($o_email->getValue(), $value);
    }

    public function provideErrorData()
    {
        return array(
            array('$%@fj'),
            array('_______________@fj'),
        );
    }

    public function provideReturnData()
    {
        return array(
            array('12345qwer@678.com'),
        );
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
