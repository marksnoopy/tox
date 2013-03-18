<?php
/**
 * Defines the test case for Tox\Core\PackageManager.
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

use org\bovigo\vfs\vfsStream;

require_once __DIR__ . '/../../../../include/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStream.php';
require_once __DIR__ . '/../../../../include/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStreamWrapper.php';
require_once __DIR__ . '/../../../../include/mikey179/vfsStream/src/main/php/org/bovigo/vfs/Quota.php';
require_once __DIR__ . '/../../../../include/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStreamContent.php';
require_once __DIR__ . '/../../../../include/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStreamAbstractContent.php';
require_once __DIR__ . '/../../../../include/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStreamContainer.php';
require_once __DIR__ . '/../../../../include/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStreamDirectory.php';
require_once __DIR__ . '/../../../../include/mikey179/vfsStream/src/main/php/org/bovigo/vfs/vfsStreamFile.php';

require_once __DIR__ . '/../../../../src/tox/core/assembly.php';
require_once __DIR__ . '/../../../../src/tox/core/packagemanager.php';

require_once __DIR__ . '/../../../../src/tox/core/exception.php';
require_once __DIR__ . '/../../../../src/tox/core/@exception/packageduplicateregistration.php';
require_once __DIR__ . '/../../../../src/tox/core/@exception/packageaccessdenied.php';
require_once __DIR__ . '/../../../../src/tox/core/@exception/illegal3rdpartypackage.php';

/**
 * Tests Tox\Core\PackageManager.
 *
 * @internal
 *
 * @package tox.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class PackageManagerTest extends PHPUnit_Framework_TestCase
{
    protected $vfs;

    protected function setUp()
    {
        $this->vfs = vfsStream::setUp('root', 0755, array(
                'include' => array(
                    'core' => array(
                        '@bootstrap.php' => '<?php $_GET[] = "Tox\\Core";',
                        '@exception' => array(
                            'blah.php' => ''
                        ),
                        'blah.php' => ''
                    ),
                    'type' => array(
                        '@bootstrap.php' => '<?php $_GET[] = "Tox\\Type";',
                        'foo' => array(
                            'bar' => array(
                                '@bootstrap.php' => '<?php $_GET[] = "Tox\\Type\\Foo\\Bar";',
                                'blah.php' => ''
                            )
                        ),
                        'bar' => array(
                            'blah.php' => ''
                        )
                    ),
                    'foo' => array(
                        '@bootstrap.php' => '<?php $_GET[] = "Tox\\Foo";'
                    )
                ),
                'lib' => array(
                )
            )
        );
    }

    public function testRegisteringAndLocating()
    {
        $o_pman = new PackageManager;
        $this->assertSame($o_pman, $o_pman->register('tox.core', vfsStream::url('root/include/core')));
        $this->assertEquals(vfsStream::url('root/include/core/blah.php'), $o_pman->locate('Tox\\Core\\Blah'));
    }

    /**
     * @depends testRegisteringAndLocating
     * @expectedException Tox\Core\PackageDuplicateRegistrationException
     */
    public function testDuplicateRegistrationForbidden()
    {
        $o_pman = new PackageManager;
        $o_pman->register('tox.core', vfsStream::url('root/include/core'))
            ->register('tox.core', __DIR__);
    }

    /**
     * @depends testDuplicateRegistrationForbidden
     * @expectedException Tox\Core\PackageDuplicateRegistrationException
     */
    public function testNamespacesRegisteredAsWish()
    {
        $o_pman = new PackageManager;
        $o_pman->register('tox.core', vfsStream::url('root/include/core'))
            ->register('Tox\\Core', __DIR__);
    }

    /**
     * @depends testRegisteringAndLocating
     */
    public function testFalseReturnedOnLocatingFailure()
    {
        $o_pman = new PackageManager;
        $o_pman->register('TOX.CORE', vfsStream::url('root/include/core'));
        $this->assertFalse($o_pman->locate('In\\Szen\\Demo\\Foo'));
    }

    /**
     * @depends testFalseReturnedOnLocatingFailure
     */
    public function testCorePackageUsedForLocatingToxFolder()
    {
        $o_pman = new PackageManager;
        $o_pman->register('TOX\\CORE', vfsStream::url('root/include/core'));
        $this->assertEquals(vfsStream::url('root/include/type/foo/bar/blah.php'),
            $o_pman->locate('Tox\\Type\\Foo\\Bar\\Blah')
        );
        $o_pman = new PackageManager;
        $o_pman->register('tox.type', vfsStream::url('root/include/type'));
        $this->assertFalse($o_pman->locate('Tox\\Core\\Blah'));
    }

    /**
     * @depends testCorePackageUsedForLocatingToxFolder
     * @depends testDuplicateRegistrationForbidden
     * @expectedException Tox\Core\PackageDuplicateRegistrationException
     */
    public function testToxRegisteredAutomaticallyByCore()
    {
        $o_pman = new PackageManager;
        $o_pman->register('TOX\\CORE', vfsStream::url('root/include/core'))
            ->register('tox', __DIR__);
    }

    /**
     * @depends testCorePackageUsedForLocatingToxFolder
     */
    public function testCorePackageMustNamedCore()
    {
        $o_pman = new PackageManager;
        $o_pman->register('tox\\core', vfsStream::url('root/include/foo'));
        $this->assertFalse($o_pman->locate('Tox\\Type\\Foo\\Bar\\Blah'));
    }

    /**
     * @depends testFalseReturnedOnLocatingFailure
     */
    public function testHigherUpsWouldNotProbed()
    {
        $o_pman = new PackageManager;
        $o_pman->register('Tox\\Type\\Foo', vfsStream::url('root/include/type/foo'));
        $this->assertFalse($o_pman->locate('Tox\\Type\\Bar\\Blah'));
    }

    /**
     * @depends testDuplicateRegistrationForbidden
     * @depends testToxRegisteredAutomaticallyByCore
     */
    public function testPackageRegisteredManuallyBeforeInUse()
    {
        $o_pman = new PackageManager;
        $o_pman->register('TOX\\CORE', vfsStream::url('root/include/core'))
            ->register('Tox\\Type\\Foo', vfsStream::url('root/include/type/bar'));
        $this->assertEquals(vfsStream::url('root/include/type/bar/blah.php'),
            $o_pman->locate('Tox\\Type\\Foo\\Blah')
        );
    }

    /**
     * @depends testPackageRegisteredManuallyBeforeInUse
     * @expectedException Tox\Core\PackageDuplicateRegistrationException
     */
    public function testUsedPackageActedAsRegistered()
    {
        $o_pman = new PackageManager;
        $o_pman->register('Tox\\Core', vfsStream::url('root/include/core'))
            ->locate('Tox\\Type\\Bar\\Blah');
        $o_pman->register('Tox\\Type', __DIR__);
    }

    /**
     * @depends testPackageRegisteredManuallyBeforeInUse
     */
    public function testLocatingFromVeryLeafRegisteredPackage()
    {
        $o_pman = new PackageManager;
        $o_pman->register('Tox.Type.Foo', vfsStream::url('root/include/foo'))
            ->register('Tox\\Core', vfsStream::url('root/include/core'));
        $this->assertEquals(vfsStream::url('root/include/foo/bar/blah.php'),
            $o_pman->locate('Tox\\Type\\Foo\\Bar\\Blah')
        );
    }

    /**
     * @depends testRegisteringAndLocating
     */
    public function testBootstrappingInLocatingNotRegsitration()
    {
        $_GET = array();
        $o_pman = new PackageManager;
        $o_pman->register('Tox\\Core', vfsStream::url('root/include/core'));
        $this->assertEmpty($_GET);
        $o_pman->locate('Tox\\Core\\Blah');
        $this->assertEquals(array('Tox\\Core'), $_GET);
    }

    /**
     * @depends testPackageRegisteredManuallyBeforeInUse
     * @depends testBootstrappingInLocatingNotRegsitration
     */
    public function testBootstrappingFromVeryRoot()
    {
        $_POST['x'] = 1;
        $o_pman = new PackageManager;
        $o_pman->register('Tox\\Core', vfsStream::url('root/include/core'))
            ->register('tox.type.foo.bar', vfsStream::url('root/include/type/foo/bar'))
            ->register('tox.type.foo', vfsStream::url('root/include/foo'))
            ->locate('Tox\\Type\\Foo\\Bar\\Blah');
        $this->assertEquals(array('Tox\\Type', 'Tox\\Foo', 'Tox\\Type\\Foo\\Bar'), $_GET);
        unset($_POST['x']);
    }

    /**
     * @depends testRegisteringAndLocating
     */
    public function testSupportingOf3rdPartyPackages()
    {
        $o_pman = new PackageManager;
        $o_pman->register('In\\Szen\\App', vfsStream::url('root/include/type'));
        $this->assertEquals(vfsStream::url('root/include/type/foo/bar/blah.php'),
            $o_pman->locate('In\\Szen\\App\\Foo\\Bar\\Blah')
        );
    }

    /**
     * @depends testSupportingOf3rdPartyPackages
     * @expectedException Tox\Core\Illegal3rdPartyPackageException
     */
    public function test3rdPartyPackagesHave3NodesAtLeast()
    {
        $o_pman = new PackageManager;
        $o_pman->register('App.type', vfsStream::url('root/include/type'));
    }

    /**
     * @depends testRegisteringAndLocating
     */
    public function testExceptionsSeparatedFromClassesAndInterfaces()
    {
        $o_pman = new PackageManager;
        $o_pman->register('Tox\\Core', vfsStream::url('root/include/core'));
        $this->assertEquals(vfsStream::url('root/include/core/@exception/blah.php'),
            $o_pman->locate('Tox\\Core\\BlahException')
        );
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
