<?php
/**
 * Defines the test case for Tox\Type\Type.
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

namespace Tox\Type;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../src/core/assembly.php';
require_once __DIR__ . '/../../../src/type/iboxable.php';
require_once __DIR__ . '/../../../src/type/type.php';

require_once __DIR__ . '/../../../src/core/exception.php';
require_once __DIR__ . '/../../../src/type/@exception/variablereteeing.php';

require_once __DIR__ . '/../../../src/type/ivarbase.php';
require_once __DIR__ . '/../../../src/type/varbase.php';

/**
 * Tests Tox\Type\Type.
 *
 * @internal
 *
 * @package tox.type
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class TypeTest extends PHPUnit_Framework_TestCase
{
    public function testValueKept()
    {
        $s_cvar = 'c1_' . md5(microtime());
        $m_value = microtime(true);
        $o_var = $this->getMockForAbstractClass('Tox\\Type\\Type', array($m_value), $s_cvar);
        $this->assertEquals($m_value, $o_var->getValue());
        $this->assertEquals($m_value, $o_var->value);
        $this->assertInternalType('string', (string) $o_var);
        $this->assertEquals(strval($m_value), (string) $o_var);
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testTypeVerification()
    {
        $o_var = & TypeStub::box(microtime());
        $this->assertInstanceOf('Tox\\Type\\TypeStub', $o_var);
        $s_ref = $o_var->getRef();
        $o_var = microtime();
        $this->assertEquals($s_ref, $o_var->getRef());
        $o_var = microtime(true);
    }

    /**
     * @expectedException Tox\Type\VariableReTeeingException
     */
    public function testSettingReferenceIDManually()
    {
        $s_cvar = 'c2_' . md5(microtime());
        $m_value = microtime(true);
        $o_var = $this->getMockForAbstractClass('Tox\\Type\\Type', array($m_value), $s_cvar);
        $this->assertNull($o_var->getRef());
        $s_ref = sha1(microtime());
        $o_vb = $this->getMock('Tox\\Type\\Varbase', array('feedRef', 'offsetExists'), array(), '', false);
        $o_vb->expects($this->once())->method('feedRef')->will($this->returnValue($s_ref));
        $o_vb->expects($this->any())->method('offsetExists')->will($this->returnValue(false));
        $this->assertEquals($s_ref, $o_var->setRef($o_vb));
        $o_var->setRef($o_vb);
    }

    public function testSerialization()
    {
        $m_value = microtime();
        $o_var1 = new TypeStub($m_value);
        $o_var2 = unserialize(serialize($o_var1));
        $this->assertInstanceOf('Tox\\Type\\TypeStub', $o_var2);
        $this->assertEquals($m_value, $o_var2->getValue());
    }

    /**
     * @depends testSerialization
     * @expectedException PHPUnit_Framework_Error
     */
    public function testValueValidationOnUnserialization()
    {
        $m_value = microtime();
        $o_var1 = new TypeStub($m_value);
        $s_var2 = str_replace(serialize($m_value), serialize(microtime(true)), serialize($o_var1));
        unserialize($s_var2);
    }

    public function testSettingThroughMagicMethods()
    {
        $m_value = microtime();
        $s_cvar = 'c3_' . md5(microtime());
        $o_var = $this->getMockBuilder('Tox\\Type\\Type')
            ->setConstructorArgs(array(microtime()))
            ->setMockClassName($s_cvar)
            ->setMethods(array('validate'))
            ->getMockForAbstractClass();
        $o_var->expects($this->once())->method('validate')->with($this->equalTo($m_value));
        $o_var->value = $m_value;
    }
}

/**
 * Represents as a boxable object type for mocking test.
 *
 * @internal
 *
 * @package tox.type
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class TypeStub extends Type
{
    /**
     * {@inheritdoc}
     *
     * @param string $value String.
     */
    protected function validate($value)
    {
        if (!is_string($value)) {
            user_error('', E_USER_ERROR);
        }
        return parent::validate($value);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
