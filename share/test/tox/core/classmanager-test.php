<?php
/**
 * Defines the test case for Tox\Core\ClassManager.
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

require_once __DIR__ . '/../../../../src/tox/core/assembly.php';
require_once __DIR__ . '/../../../../src/tox/core/classmanager.php';

require_once __DIR__ . '/../../../../src/tox/core/exception.php';
require_once __DIR__ . '/../../../../src/tox/core/@exception/existantclasstoalias.php';

/**
 * Tests Tox\Core\ClassManager.
 *
 * @internal
 *
 * @package tox.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ClassManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * Stores the target instance.
     *
     * @var ClassManager
     */
    protected $cman;

    /**
     * Prepares for each test.
     */
    protected function setUp()
    {
        $this->cman = new ClassManager;
    }

    /**
     * @expectedException Tox\Core\ExistantClassToAliasException
     */
    public function testNamesOfExistantClassesCannotBeRealiased()
    {
        $this->cman->alias(__CLASS__, 'Tox\\Core\\ClassManager');
    }

    /**
     * @depends testNamesOfExistantClassesCannotBeRealiased
     */
    public function testAliasWouldNotBeAffectedImmediately()
    {
        $s_class1 = 'c' . md5(microtime());
        $s_class2 = 'c' . md5(microtime());
        $this->assertSame($this->cman->alias($s_class1, $s_class2), $this->cman);
        $this->assertFalse(class_exists($s_class2, false));
    }

    /**
     * @depends testAliasWouldNotBeAffectedImmediately
     */
    public function testAliasToExistantAssemblyAffectedImmediately()
    {
        $s_class = 'c' . md5(microtime());
        $this->cman->alias(__CLASS__, $s_class);
        $this->assertTrue(class_exists($s_class));
    }

    /**
     * @depends testAliasWouldNotBeAffectedImmediately
     */
    public function testTransformAliases()
    {
        $s_class = 'c' . md5(microtime());
        $this->assertEquals(__CLASS__, $this->cman->alias(__CLASS__, $s_class)->transform($s_class));
    }

    /**
     * @depends testTransformAliases
     */
    public function testChainedAliasesPermitted()
    {
        $s_class1 = 'c' . md5(microtime());
        $s_class2 = 'c' . md5(microtime());
        $this->assertEquals(__CLASS__,
            $this->cman->alias(__CLASS__, $s_class1)->alias($s_class1, $s_class2)->transform($s_class2)
        );
    }

    /**
     * @depends testChainedAliasesPermitted
     */
    public function testAliasAffectedAfterRegister()
    {
        $s_class1 = 'c' . md5(microtime());
        $s_class2 = 'c' . md5(microtime());
        $this->cman->alias(__CLASS__, $s_class1)->alias($s_class1, $s_class2)->register(__CLASS__, __FILE__);
        $this->assertTrue(class_exists($s_class1));
        $this->assertTrue(class_exists($s_class2));
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
