<?php
/**
 * Defines the test case for Tox\Core\Assembly.
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

use Exception;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/tox/core/assembly.php';

require_once __DIR__ . '/../../../../src/tox/core/exception.php';
require_once __DIR__ . '/../../../../src/tox/core/propertyreaddeniedexception.php';
require_once __DIR__ . '/../../../../src/tox/core/propertywritedeniedexception.php';

/**
 * Tests Tox\Core\Assembly.
 *
 * @internal
 *
 * @package tox.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class AssemblyTest extends PHPUnit_Framework_TestCase
{
    public function testGetterMechanismSupported()
    {
        try {
            $s_prop = md5(microtime());
            $o_mock = $this->getMock('Tox\\Core\\AssemblyMockA',
                array('_tox_isMagicPropReadable', '__get' . $s_prop)
            );
            $o_mock->expects($this->once())->method('_tox_isMagicPropReadable')
                ->with($this->equalTo($s_prop))
                ->will($this->returnValue(TRUE));
            $o_mock->expects($this->once())->method('__get' . $s_prop);
            $o_mock->$s_prop;
        } catch (Exception $ex) {
            $this->fail();
        }
    }

    /**
     * @depends testGetterMechanismSupported
     */
    public function testRegularPairOfPropAndGetterWouldWorkFine()
    {
        try {
            $o_obj = new AssemblyMockA;
            $this->assertNull($o_obj->ok);
        } catch (Exception $ex) {
            $this->fail();
        }
    }

    /**
     * @depends testRegularPairOfPropAndGetterWouldWorkFine
     * @expectedException Tox\Core\PropertyReadDeniedException
     */
    public function testPropertyMustBeDeclaredForGetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->noProp;
    }

    /**
     * @depends testRegularPairOfPropAndGetterWouldWorkFine
     * @expectedException Tox\Core\PropertyReadDeniedException
     */
    public function testGetterMustBeDeclaredForGetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->noGetter;
    }

    /**
     * @depends testRegularPairOfPropAndGetterWouldWorkFine
     * @expectedException Tox\Core\PropertyReadDeniedException
     */
    public function testInternalPropWhichLeadingWithUnderlineAgainstGetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->_inProp;
    }

    /**
     * @depends testRegularPairOfPropAndGetterWouldWorkFine
     * @expectedException Tox\Core\PropertyReadDeniedException
     */
    public function testPrivatePropAgainstGetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->privProp;
    }

    /**
     * @depends testRegularPairOfPropAndGetterWouldWorkFine
     * @expectedException Tox\Core\PropertyReadDeniedException
     */
    public function testPublicGetterAgainstGetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->pubGetter;
    }

    public function testSetterMechanismSupported()
    {
        try {
            $s_prop = md5(microtime());
            $f_value = microtime(TRUE);
            $o_mock = $this->getMock('Tox\\Core\\AssemblyMockA', array('_tox_isMagicPropWritable', '__set' . $s_prop));
            $o_mock->expects($this->once())->method('_tox_isMagicPropWritable')
                ->with($this->equalTo($s_prop))
                ->will($this->returnValue(TRUE));
            $o_mock->expects($this->once())->method('__set'. $s_prop)
                ->with($this->equalTo($f_value));
            $o_mock->$s_prop = $f_value;
        } catch (Exception $ex) {
            $this->fail();
        }
    }

    /**
     * @depends testSetterMechanismSupported
     * @depends testRegularPairOfPropAndGetterWouldWorkFine
     */
    public function testRegularPairOfPropAndSetterWouldWorkFine()
    {
        try {
            $f_value = microtime(TRUE);
            $o_obj = new AssemblyMockA;
            $o_obj->ok = $f_value;
            $this->assertEquals($f_value, $o_obj->ok);
        } catch (Exception $ex) {
            $this->fail();
        }
    }

    /**
     * @depends testSetterMechanismSupported
     * @expectedException Tox\Core\PropertyWriteDeniedException
     */
    public function testPropertyMustBeDeclaredForSetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->noProp = 1;
    }

    /**
     * @depends testSetterMechanismSupported
     * @expectedException Tox\Core\PropertyWriteDeniedException
     */
    public function testSetterMustBeDeclaredForSetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->noSetter = 1;
    }

    /**
     * @depends testSetterMechanismSupported
     * @expectedException Tox\Core\PropertyWriteDeniedException
     */
    public function testInternalPropWhichLeadingWithUnderlineAgainstSetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->_inProp = 1;
    }

    /**
     * @depends testSetterMechanismSupported
     * @expectedException Tox\Core\PropertyWriteDeniedException
     */
    public function testPrivatePropAgainstSetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->privProp = 1;
    }

    /**
     * @depends testSetterMechanismSupported
     * @expectedException Tox\Core\PropertyWriteDeniedException
     */
    public function testPublicSetterAgainstSetterMechanism()
    {
        $o_obj = new AssemblyMockA;
        $o_obj->pubSetter = 1;
    }

    /**
     * @depends testSetterMechanismSupported
     */
    public function testDifferentTypesKeepTheirOwnPropertiesInformations()
    {
        try {
            $f_value = microtime(TRUE);
            $o_obj1 = new AssemblyMockA;
            $o_obj2 = new AssemblyMockB;
            $o_obj1->ok = $f_value;
            $o_obj2->foo = $f_value;
            $this->assertEquals($f_value, $o_obj1->ok);
            $this->assertNull($o_obj2->foo);
        } catch (Exception $ex) {
            $this->fail();
        }
    }
}

/**
 * Represents as an assembly for mocking test.
 *
 * @internal
 *
 * @package tox.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 *
 * @property mixed $ok
 */
class AssemblyMockA extends Assembly
{
    /**
     * Retrieves and sets some value.
     *
     * @var mixed
     */
    protected $ok;

    /**
     * Handles to retrieve some value.
     *
     * @return mixed
     */
    protected function __getOk()
    {
        return $this->ok;
    }

    /**
     * Handles to set some value.
     *
     * @param  mixed $value
     * @return void
     */
    protected function __setOk($value)
    {
        $this->ok = $value;
    }

    /**
     * Acts as an illegal getter for no corresponding property.
     *
     * @return void
     */
    protected function __getNoProp()
    {
    }

    /**
     * Acts as an illegal setter for no corresponding property.
     *
     * @param  mixed $value
     * @return void
     */
    protected function __setNoProp($value)
    {
    }

    /**
     * Acts as an illegal property for no corresponding getter.
     *
     * @var NULL
     */
    protected $noGetter;

    /**
     * Acts as an illegal property for no corresponding setter.
     *
     * @var NULL
     */
    protected $noSetter;

    /**
     * Acts as an illegal property for leading with an underline.
     *
     * @var NULL
     */
    protected $_inProp;

    /**
     * Acts as an illegal getter for the corresponding property leading with an
     * underline.
     *
     * @return void
     */
    protected function __get_inProp()
    {
    }

    /**
     * Acts as an illegal setter for the corresponding property leading with an
     * underline.
     *
     * @param  mixed $value
     * @return void
     */
    protected function __set_inProp($value)
    {
    }

    /**
     * Acts as an illegal property for private visibility.
     *
     * @var NULL
     */
    private $privProp;

    /**
     * Acts as an illegal getter for the corresponding property is private.
     *
     * @return void
     */
    protected function __getPrivProp()
    {
    }

    /**
     * Acts as an illegal setter for the corresponding property is private.
     *
     * @param  mixed $value
     * @return void
     */
    protected function __setPrivProp($value)
    {
    }

    /**
     * Acts as an illegal property for the corresponding getter is public.
     *
     * @var NULL
     */
    protected $pubGetter;

    /**
     * Acts as an illegal getter for the public visibility.
     *
     * @return void
     */
    public function __getPubGetter()
    {
    }

    /**
     * Acts as an illegal property for the corresponding setter is public.
     *
     * @var NULL
     */
    protected $pubSetter;

    /**
     * Acts as an illegal setter for the public visibility.
     *
     * @param  mixed $value
     * @return void
     */
    public function __setPubSetter($value)
    {
    }
}

/**
 * Represents as another assembly for mocking test.
 *
 * @internal
 *
 * @package tox.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 *
 * @property NULL $foo
 */
class AssemblyMockB extends Assembly
{
    /**
     * Retrieves and sets some value.
     *
     * @var mixed
     */
    protected $foo;

    /**
     * Handles to retrieve some value.
     *
     * @return mixed
     */
    protected function __getFoo()
    {
    }

    /**
     * Handles to set some value.
     *
     * @param  mixed $value
     * @return void
     */
    protected function __setFoo($value)
    {
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
