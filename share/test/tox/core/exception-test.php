<?php
/**
 * Defines the test case for Tox\Core\Exception.
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

namespace Tox\Core;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/tox/core/exception.php';

use Exception as PHPException;

/**
 * Tests Tox\Core\Exception.
 *
 * @internal
 *
 * @package toxtest.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * Stores the ExceptionMock instance to be test.
     *
     * @var ExceptionMock
     */
    protected $ex;

    /**
     * Stores the context informations.
     *
     * @var mixed[]
     */
    protected $ctx;

    /**
     * Stores the linked exception.
     *
     * @var PHPException
     */
    protected $pex;

    /**
     * Prepares for each test.
     */
    protected function setUp()
    {
        $this->ctx = array(
            'foo' => microtime(),
            'bar' => microtime(true)
        );
        $this->pex = new PHPException;
        $this->ex = new ExceptionMock($this->ctx, $this->pex);
    }

    /**
     * @dataProvider repeat10Times
     */
    public function testSameCodeForEveryInstance()
    {
        $this->assertEquals(0x1000000, $this->ex->getCode());
    }

    public function testPassedinContextKept()
    {
        $this->assertTrue(is_callable(array($this->ex, 'getContext')));
        $this->assertEquals($this->ctx, $this->ex->getContext());
    }

    /**
     * @dataProvider repeat10Times
     */
    public function testContextualMessageForEachInstance()
    {
        $s_exp = sprintf('Bar %32.8f, foo %032s.', $this->ctx['bar'], $this->ctx['foo']);
        $this->assertEquals($s_exp, $this->ex->getMessage());
    }

    public function testLinkedExceptionKept()
    {
        $this->assertInstanceOf('Exception', $this->ex->getPrevious());
        $o_ex = new ExceptionMock($this->pex);
        $this->assertSame($this->pex, $o_ex->getPrevious());
    }

    /**
     * Be used to repeat a test 10 times.
     */
    public function repeat10Times()
    {
        return array(
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            array()
        );
    }
}

/**
 * Represents as an exception for mocking test.
 *
 * @internal
 *
 * @package Tox\Core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ExceptionMock extends Exception
{
    /**
     * Represents the code of this type of exceptions.
     *
     * @var int
     */
    const CODE = 0x81000000;

    /**
     * Represents the original message of this type of exceptions.
     *
     * @var string
     */
    const MESSAGE = 'bar %bar$32.8f, foo %foo$032s';
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
