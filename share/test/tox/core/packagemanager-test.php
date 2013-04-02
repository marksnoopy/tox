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

if (!defined('DIR_VFSSTREAM')) {
    define('DIR_VFSSTREAM', __DIR__ . '/../../../../include/mikey179/vfsStream/src/main/php/org/bovigo/vfs');
}

require_once DIR_VFSSTREAM . '/vfsStream.php';
require_once DIR_VFSSTREAM . '/vfsStreamWrapper.php';
require_once DIR_VFSSTREAM . '/Quota.php';
require_once DIR_VFSSTREAM . '/vfsStreamContent.php';
require_once DIR_VFSSTREAM . '/vfsStreamAbstractContent.php';
require_once DIR_VFSSTREAM . '/vfsStreamContainer.php';
require_once DIR_VFSSTREAM . '/vfsStreamDirectory.php';
require_once DIR_VFSSTREAM . '/vfsStreamFile.php';

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
    /**
     * Stores the target instance.
     *
     * @var PackageManager
     */
    protected $pman;

    /**
     * Stores the virtual file system.
     *
     * @var vfsStream
     */
    protected $vfs;

    /**
     * Stores the root folder name.
     *
     * WARNING: Randomize names are used to ignore the `require_once()`
     * machenism.
     *
     * @var string
     */
    protected $root;

    /**
     * Stores the logs from simulate bootstrappers.
     *
     * @var string[]
     */
    protected static $log;

    /**
     * Be used for simulate bootstrappers.
     *
     * @param  string $lob Log content.
     * @return void
     */
    public static function log($lob)
    {
        self::$log[] = (string) $lob;
    }

    protected function setUp()
    {
        self::$log = array();
        $this->pman = new PackageManager;
        $this->root = md5(microtime());
        $this->vfs = vfsStream::setUp(
            $this->root,
            0755,
            array(
                'include' => array(
                    'core' => array(
                        '@bootstrap.php' => '<?php Tox\\Core\\PackageManagerTest::log("Tox\\Core");',
                        '@exception' => array(
                            'blah.php' => ''
                        ),
                        'blah.php' => ''
                    ),
                    'type' => array(
                        '@bootstrap.php' => '<?php Tox\\Core\\PackageManagerTest::log("Tox\\Type");',
                        '@exception' => array(
                            'blah.php' => ''
                        ),
                        'foo' => array(
                            'bar' => array(
                                '@bootstrap.php' => '<?php Tox\\Core\\PackageManagerTest::log("Tox\\Type\\Foo\\Bar");',
                                'blah.php' => ''
                            )
                        ),
                        'bar' => array(
                            'blah.php' => ''
                        ),
                        'blahexception.php' => ''
                    ),
                    'foo' => array(
                        '@bootstrap.php' => '<?php Tox\\Core\\PackageManagerTest::log("Tox\\Foo");'
                    ),
                    'blah.php' => ''
                ),
                'lib' => array(
                )
            )
        );
    }

    public function testRegisteringAndLocating()
    {
        $this->assertSame(
            $this->pman,
            $this->pman->register('tox.core', vfsStream::url($this->root . '/include/core'))
        );
        $this->assertEquals(
            vfsStream::url($this->root . '/include/core/blah.php'),
            $this->pman->locate('Tox\\Core\\Blah')
        );
    }

    /**
     * @depends testRegisteringAndLocating
     * @expectedException Tox\Core\PackageDuplicateRegistrationException
     */
    public function testDuplicateRegistrationForbidden()
    {
        $this->pman->register('tox.core', vfsStream::url($this->root . '/include/core'))
            ->register('tox.core', __DIR__);
    }

    /**
     * @depends testDuplicateRegistrationForbidden
     * @expectedException Tox\Core\PackageDuplicateRegistrationException
     */
    public function testNamespacesRegisteredAsWish()
    {
        $this->pman->register('tox.core', vfsStream::url($this->root . '/include/core'))
            ->register('Tox\\Core', __DIR__);
    }

    /**
     * @depends testRegisteringAndLocating
     */
    public function testFalseReturnedOnLocatingFailure()
    {
        $this->pman->register('TOX.CORE', vfsStream::url($this->root . '/include/core'));
        $this->assertFalse($this->pman->locate('In\\SZen\\Demo\\Foo'));
    }

    /**
     * @depends testFalseReturnedOnLocatingFailure
     */
    public function testCorePackageUsedForLocatingToxFolder()
    {
        $this->pman->register('TOX\\CORE', vfsStream::url($this->root . '/include/core'));
        $this->assertEquals(
            vfsStream::url($this->root . '/include/type/foo/bar/blah.php'),
            $this->pman->locate('Tox\\Type\\Foo\\Bar\\Blah')
        );
        $this->pman = new PackageManager;
        $this->pman->register('tox.type', vfsStream::url($this->root . '/include/type'));
        $this->assertFalse($this->pman->locate('Tox\\Core\\Blah'));
    }

    /**
     * @depends testCorePackageUsedForLocatingToxFolder
     * @depends testDuplicateRegistrationForbidden
     * @expectedException Tox\Core\PackageDuplicateRegistrationException
     */
    public function testToxRegisteredAutomaticallyByCore()
    {
        $this->pman->register('TOX\\CORE', vfsStream::url($this->root . '/include/core'))
            ->register('tox', __DIR__);
    }

    /**
     * @depends testCorePackageUsedForLocatingToxFolder
     */
    public function testCorePackageMustNamedCore()
    {
        $this->pman->register('tox\\core', vfsStream::url($this->root . '/include/foo'));
        $this->assertFalse($this->pman->locate('Tox\\Type\\Foo\\Bar\\Blah'));
    }

    /**
     * @depends testFalseReturnedOnLocatingFailure
     */
    public function testHigherUpsWouldNotProbed()
    {
        $this->pman->register('Tox\\Type\\Foo', vfsStream::url($this->root . '/include/type/foo'));
        $this->assertFalse($this->pman->locate('Tox\\Type\\Bar\\Blah'));
    }

    /**
     * @depends testDuplicateRegistrationForbidden
     * @depends testToxRegisteredAutomaticallyByCore
     */
    public function testPackageRegisteredManuallyBeforeInUse()
    {
        $this->pman->register('TOX\\CORE', vfsStream::url($this->root . '/include/core'))
            ->register('Tox\\Type\\Foo', vfsStream::url($this->root . '/include/type/bar'));
        $this->assertEquals(
            vfsStream::url($this->root . '/include/type/bar/blah.php'),
            $this->pman->locate('Tox\\Type\\Foo\\Blah')
        );
    }

    /**
     * @depends testPackageRegisteredManuallyBeforeInUse
     * @expectedException Tox\Core\PackageDuplicateRegistrationException
     */
    public function testUsedPackageActedAsRegistered()
    {
        $this->pman->register('Tox\\Core', vfsStream::url($this->root . '/include/core'))
            ->locate('Tox\\Type\\Bar\\Blah');
        $this->pman->register('Tox\\Type', __DIR__);
    }

    /**
     * @depends testPackageRegisteredManuallyBeforeInUse
     */
    public function testLocatingFromVeryLeafRegisteredPackage()
    {
        $this->pman->register('Tox.Type.Foo', vfsStream::url($this->root . '/include/foo'))
            ->register('Tox\\Core', vfsStream::url($this->root . '/include/core'));
        $this->assertEquals(
            vfsStream::url($this->root . '/include/foo/blah.php'),
            $this->pman->locate('Tox\\Type\\Foo\\Blah')
        );
    }

    /**
     * @depends testLocatingFromVeryLeafRegisteredPackage
     */
    public function testEveryHigherUpMustBeSeekableForTox()
    {
        $this->pman->register('tox.bar.foo', vfsStream::url($this->root . '/include/foo'))
            ->register('tox.core', vfsStream::url($this->root . '/include/core'));
        $this->assertFalse($this->pman->locate('Tox\\Bar\\Foo\\Bar\\Blah'));
    }

    /**
     * @depends testRegisteringAndLocating
     */
    public function testBootstrappingInLocatingNotRegsitration()
    {
        $this->pman->register('Tox\\Core', vfsStream::url($this->root . '/include/core'));
        $this->assertEmpty(self::$log);
        $this->pman->locate('Tox\\Core\\Blah');
        $this->assertEquals(array('Tox\\Core'), self::$log);
    }

    /**
     * @depends testPackageRegisteredManuallyBeforeInUse
     * @depends testBootstrappingInLocatingNotRegsitration
     */
    public function testBootstrappingFromVeryRoot()
    {
        $this->pman->register('Tox\\Core', vfsStream::url($this->root . '/include/core'))
            ->register('tox.type.foo.bar', vfsStream::url($this->root . '/include/type/foo/bar'))
            ->register('tox.type.foo', vfsStream::url($this->root . '/include/foo'))
            ->locate('Tox\\Type\\Foo\\Bar\\Blah');
        $this->assertEquals(array('Tox\\Type', 'Tox\\Foo', 'Tox\\Type\\Foo\\Bar'), self::$log);
    }

    /**
     * @depends testRegisteringAndLocating
     */
    public function testSupportingOf3rdPartyPackages()
    {
        $this->pman->register('In\\SZen\\App', vfsStream::url($this->root . '/include/type'))
            ->register('in.szen.app2.api.new', vfsStream::url($this->root . '/include/type'));
        $this->assertEquals(
            vfsStream::url($this->root . '/include/type/foo/bar/blah.php'),
            $this->pman->locate('In\\SZen\\App\\Foo\\Bar\\Blah')
        );
        $this->assertEquals(
            vfsStream::url($this->root . '/include/type/foo/bar/blah.php'),
            $this->pman->locate('In\\SZen\\App2\\API\\New\\Foo\\Bar\\Blah')
        );
    }

    /**
     * @depends testSupportingOf3rdPartyPackages
     * @expectedException Tox\Core\Illegal3rdPartyPackageException
     */
    public function test3rdPartyPackagesHave3NodesAtLeast()
    {
        $this->pman->register('App.type', vfsStream::url($this->root . '/include/type'));
    }

    /**
     * @depends testLocatingFromVeryLeafRegisteredPackage
     * @depends test3rdPartyPackagesHave3NodesAtLeast
     */
    public function testEveryHigherUpSubOfRegistered3rdPartyPackageMustBeSeekable()
    {
        $this->pman->register('in.szen.app', vfsStream::url($this->root . '/include/core'))
            ->register('in.szen.app.web.dao', vfsStream::url($this->root . '/include/type'));
        $this->assertFalse($this->pman->locate('In\\SZen\\App\\Web\\Dao\\Blah'));
    }

    /**
     * @depends testRegisteringAndLocating
     *
     * @deprecated
     */
    public function testExceptionsSeparatedFromClassesAndInterfaces()
    {
        $this->pman->register('Tox\\Core', vfsStream::url($this->root . '/include/core'));
        $this->assertEquals(
            vfsStream::url($this->root . '/include/core/@exception/blah.php'),
            $this->pman->locate('Tox\\Core\\BlahException')
        );
    }

    /**
     * @depends testRegisteringAndLocating
     */
    public function testClassNamesHave3NodesAtLeast()
    {
        $this->assertFalse($this->pman->locate('Smarty'));
        $this->pman->register('Tox\\Core', vfsStream::url($this->root . '/include/core'));
        $this->assertFalse($this->pman->locate('Tox\\Blah'));
    }

    /**
     * @depends testRegisteringAndLocating
     */
    public function testExceptionsFollowingPSR0()
    {
        $this->pman->register('Tox\\Core', vfsStream::url($this->root . '/include/core'));
        $this->assertEquals(
            vfsStream::url($this->root . '/include/type/blahexception.php'),
            $this->pman->locate('Tox\\Type\\BlahException')
        );
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
