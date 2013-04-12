<?php
/**
 * Defines the test case for Tox\Type\Varbase.
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

namespace Tox\Type;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../src/core/assembly.php';
require_once __DIR__ . '/../../../src/type/ivarbase.php';
require_once __DIR__ . '/../../../src/type/varbase.php';

require_once __DIR__ . '/../../../src/core/exception.php';
require_once __DIR__ . '/../../../src/type/@exception/varbaseunderattack.php';

require_once __DIR__ . '/../../../src/type/iboxable.php';
require_once __DIR__ . '/../../../src/type/type.php';

/**
 * Tests Tox\Type\Varbase.
 *
 * @internal
 *
 * @package tox.type
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class VarbaseTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        VarbaseMock::reset();
    }

    public function testTeeing()
    {
        $s_cvar = 'c1_' . md5(microtime());
        $s_ref = sha1(microtime());
        $o_var = $this->getMockForAbstractClass('Tox\\Type\\Type', array(), $s_cvar, false);
        $o_vb = $this->getMock('Tox\\Type\\VarbaseMock', array('feedRef'), array(), '', false);
        $o_vb->construct()->expects($this->once())->method('feedRef')
            ->will($this->returnValue($s_ref));
        $this->assertCount(0, $o_vb);
        VarbaseMock::setInstance($o_vb);
        $this->assertSame($o_var, VarbaseMock::tee($o_var));
        $this->assertCount(1, $o_vb);
        $this->assertTrue(isset($o_vb[$s_ref]));
        $this->assertSame($o_var, $o_vb[$s_ref]);
    }

    /**
     * @depends testTeeing
     */
    public function testAutoBoxing()
    {
        $s_cvar = 'c2_' . md5(microtime());
        $o_var = $this->getMockForAbstractClass('Tox\\Type\\Type', array(), $s_cvar, false);
        $m_value1 = microtime();
        $m_value2 = microtime();
        $o_value = & $o_var::box($m_value1);
        $s_ref1 = $o_value->getRef();
        $o_value = $m_value2;
        $this->assertInstanceOf($s_cvar, $o_value);
        $this->assertEquals($s_ref1, $o_value->getRef());
        $this->assertEquals($m_value2, $o_value->getValue());
    }

    /**
     * @depends testAutoBoxing
     */
    public function testVarbaseWouldNotScaledOnUnset()
    {
        $s_cvar = 'c3_' . md5(microtime());
        $o_vb = $this->getMock('Tox\\Type\\VarbaseMock', array('feedRef'), array(), '', false);
        $o_vb->construct();
        VarbaseMock::setInstance($o_vb);
        $o_var = $this->getMockForAbstractClass('Tox\\Type\\Type', array(), $s_cvar, false);
        $o_value = & $o_var::box(microtime());
        unset($o_value);
        $this->assertCount(1, $o_vb);
    }

    /**
     * @depends testVarbaseWouldNotScaledOnUnset
     */
    public function testGarbageCollection()
    {
        $s_cvar = 'c4_' . md5(microtime());
        $o_vb = $this->getMock('Tox\\Type\\VarbaseMock', array('feedRef'), array(), '', false);
        $o_vb->construct();
        VarbaseMock::setInstance($o_vb);
        $o_var = $this->getMockForAbstractClass('Tox\\Type\\Type', array(''), $s_cvar, false);
        $o_value = & $o_var::box(microtime());
        unset($o_value);
        VarbaseMock::gc();
        $this->assertCount(0, $o_vb);
    }

    /**
     * @extends testAutoBoxing
     * @expectedException Tox\Type\VarbaseUnderAttackException
     */
    public function testExceptionRaisedOnHackingIntoVarbase()
    {
        $s_cvar = 'c5_' . md5(microtime());
        $s_ref = sha1(microtime());
        $o_vb = $this->getMock('Tox\\Type\\VarbaseMock', array('feedRef'), array(), '', false);
        $o_vb->construct()->expects($this->once())->method('feedRef')
            ->will($this->returnValue($s_ref));
        VarbaseMock::setInstance($o_vb);
        $o_var = $this->getMockForAbstractClass('Tox\\Type\\Type', array(''), $s_cvar, false);
        $o_value = & $o_var::box(microtime());
        $o_vb[$s_ref] = $this->getMockForAbstractClass('Tox\\Type\\Type', array(microtime()), $s_cvar, false);
    }

    public function testNullReturnedForRetrievingNonExistatntObject()
    {
        $o_vb = VarbaseMock::getInstance();
        $s_id = sha1(microtime());
        $this->assertNull($o_vb[$s_id]);
    }

    /**
     * @depends testAutoBoxing
     */
    public function testNothingRemovedForUnsetting()
    {
        $s_cvar = 'c6_' . md5(microtime());
        $o_var = $this->getMockForAbstractClass('Tox\\Type\\Type', array(), $s_cvar, false);
        $o_vb = VarbaseMock::getInstance();
        $o_value = & $o_var::box(microtime());
        $s_ref = $o_value->getRef();
        unset($o_vb[$s_ref]);
        $this->assertTrue(isset($o_vb[$s_ref]));
    }
}

/**
 * Represents as a runtime variables manager for mocking tests.
 *
 * @internal
 *
 * @package tox.type
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class VarbaseMock extends Varbase
{
    /**
     * Resets for each testing case.
     *
     * @return void
     */
    public static function reset()
    {
        self::$instance = null;
    }

    /**
     * Overwrites the instance.
     *
     * @param  VarbaseMock $varbase Mocking instance.
     * @return void
     */
    public static function setInstance(VarbaseMock $varbase)
    {
        self::$instance = $varbase;
    }

    /**
     * Retrieves the instance.
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static;
        }
        return self::$instance;
    }

    /**
     * Constructs for the real behaviors.
     *
     * @return self
     */
    public function construct()
    {
        $this->__construct();
        return $this;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
