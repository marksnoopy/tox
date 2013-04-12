<?php
/**
 * Defines the test case for Tox\Application\Controller\Controller.
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

namespace Tox\Application\Configuration;

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

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/application/iconfiguration.php';
require_once __DIR__ . '/../../../../src/application/configuration/configuration.php';
require_once __DIR__ . '/../../../../src/application/configuration/@exception/invalidconfigurationfile.php';
require_once __DIR__ . '/../../../../src/application/configuration/@exception/invalidconfigurationitems.php';

require_once __DIR__ . '/../../../../src/application/application.php';

use Tox;

/**
 * Tests Tox\Application\Configuration\Configuration.
 *
 * @internal
 *
 * @package tox.application.configuration
 * @author  Trainxy Ho <trainxy@gmail.com>
 */
class ConfigurationTest extends PHPUnit_Framework_TestCase
{

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

    protected $cwd;

    public function setUp()
    {
        $this->cwd = getcwd();
        $this->root = md5(microtime());
        $this->vfs = vfsStream::setUp(
            $this->root,
            0755,
            array(
                'etc' => array(
                    'essential.conf.php' => '<?php $a_array = array("domain" => "9apps.mobi",' .
                        ' "package-name" => "com.nineapps.android");',
                    'import.conf.php' => '<?php $a_array = array("domain" => "google.com",' .
                        ' "package-name" => "com.google.android");',
                    'constant.conf.php' => '<?php $a_array = array("page-size" => "20", "suffix" => "apk");',
                    'db.conf.php' => '<?php $array = array("abc" => "value abc", "bcd" => "value bcd");',
                    'memcache.conf.php' => '<?php $a_array = array("memcache" => array("host" => "xxx",' .
                        ' "port" => "11211"));',
                    'default.conf.php' => '<?php $a_array = array("aaa" => "value aaa", "bbb" => "value bbb");',
                    'var.conf.php' => '<?php $a_array = "abc";',
                    'export.conf.php' => '<?php $a_array = array("abw9j" => "ababsdf2", "cd" => "cdcd",' .
                        ' "ab2ee" => "ab2ee", "ee" => "eeee");',
                )
            )
        );
    }

    public function tearDown()
    {
        chdir($this->cwd);
    }

    /**
     * @dataProvider legalConfigurationFile
     */
    public function testInitConfigurationWouldBeFine($path, $configs)
    {
        $o_configuration = $this->getMockBuilder('Tox\\Application\\Configuration\\Configuration')
            ->setMethods(array('getPath'))
            ->disableOriginalConstructor()
            ->getMock();
        $o_configuration->expects($this->once())
            ->method('getPath')
            ->with($this->equalTo($path))
            ->will($this->returnValue(vfsStream::url($this->root . $path)));
        $o_configuration->__construct($path);
        $o_expected_config = json_decode($configs, true);

        /**
         * @XXX Can not assertEqual an object (implemented ArrayAccess) and an array, use loop to instead.
         */
        foreach ($o_expected_config as $k => $v) {
            $this->assertEquals($o_expected_config[$k], $o_configuration[$k]);
        }
    }

    /**
     * @dataProvider legalConfigurationFile
     */
    public function testImportConfigurationWouldBeFine($path, $configs)
    {
        $o_configuration = $this->getMockBuilder('Tox\\Application\\Configuration\\Configuration')
            ->setMethods(array('getPath'))
            ->disableOriginalConstructor()
            ->getMock();
        $o_configuration->expects($this->once())
            ->method('getPath')
            ->with($this->equalTo($path))
            ->will($this->returnValue(vfsStream::url($this->root . $path)));
        $o_configuration->import($path);
        $o_expected_config = json_decode($configs, true);

        /**
         * @XXX Can not assertEqual an object (implemented ArrayAccess) and an array, use loop to instead.
         */
        foreach ($o_expected_config as $k => $v) {
            $this->assertEquals($o_expected_config[$k], $o_configuration[$k]);
        }
    }

    /**
     * @depends testInitConfigurationWouldBeFine
     * @dataProvider invalidConfigurationFile
     * @expectedException Tox\Application\Configuration\InvalidConfigurationFileException
     */
    public function testInvalidConfiguratoinFileWouldNotBeInited($path)
    {
        $o_configuration = $this->getMockBuilder('Tox\\Application\\Configuration\\Configuration')
            ->setMethods(array('getPath'))
            ->disableOriginalConstructor()
            ->getMock();
        $o_configuration->expects($this->once())
            ->method('getPath')
            ->with($this->equalTo($path))
            ->will($this->returnValue(vfsStream::url($this->root . $path)));
        $o_configuration->__construct($path);
    }

    /**
     * @depends testInitConfigurationWouldBeFine
     * @dataProvider invalidConfigurationFile
     * @expectedException Tox\Application\Configuration\InvalidConfigurationFileException
     */
    public function testInvalidConfiguratoinFileWouldNotBeImported($path)
    {
        $o_configuration = $this->getMockBuilder('Tox\\Application\\Configuration\\Configuration')
            ->setMethods(array('getPath'))
            ->disableOriginalConstructor()
            ->getMock();
        $o_configuration->expects($this->once())
            ->method('getPath')
            ->with($this->equalTo($path))
            ->will($this->returnValue(vfsStream::url($this->root . $path)));
        $o_configuration->import($path);
    }


    /**
     * @depends testInitConfigurationWouldBeFine
     */
    public function testLoadedOrSettedItemsWouldOverWriteInitedItems()
    {
        $path = '/etc/essential.conf.php';
        $o_configuration = $this->getMockBuilder('Tox\\Application\\Configuration\\Configuration')
            ->setMethods(array('getPath'))
            ->disableOriginalConstructor()
            ->getMock();
        $o_configuration->expects($this->once())
            ->method('getPath')
            ->with($this->equalTo($path))
            ->will($this->returnValue(vfsStream::url($this->root . $path)));
        $o_configuration->__construct($path);

        $o_configuration->load(array('domain' => 'abc', 'package-name' => 'bcd'));
        $this->assertEquals('abc', $o_configuration['domain']);
        $this->assertEquals('bcd', $o_configuration['package-name']);

        $o_configuration->set('domain', 'google.com');
        $o_configuration->set('package-name', 'com.google.android');

        $this->assertTrue(isset($o_configuration['domain']));
        $this->assertEquals('google.com', $o_configuration['domain']);
        $this->assertEquals('com.google.android', $o_configuration['package-name']);

    }

    /**
     * @depends testInitConfigurationWouldBeFine
     * @depends testImportConfigurationWouldBeFine
     */
    public function testImportedItemsWouldNotOverWriteInitedItems()
    {
        $path = '/etc/essential.conf.php';
        $import_path = '/etc/import.conf.php';

        $o_configuration = $this->getMockBuilder('Tox\\Application\\Configuration\\Configuration')
            ->setMethods(array('getPath'))
            ->disableOriginalConstructor()
            ->getMock();

        $o_configuration->expects($this->at(0))
            ->method('getPath')
            ->with($this->equalTo($path))
            ->will($this->returnValue(vfsStream::url($this->root . $path)));
        $o_configuration->expects($this->at(1))
            ->method('getPath')
            ->with($this->equalTo($import_path))
            ->will($this->returnValue(vfsStream::url($this->root . $import_path)));

        $o_configuration->__construct($path);
        $this->assertEquals('9apps.mobi', $o_configuration['domain']);
        $this->assertEquals('com.nineapps.android', $o_configuration['package-name']);

        $o_configuration->import($import_path);
        $this->assertEquals('9apps.mobi', $o_configuration['domain']);
        $this->assertEquals('com.nineapps.android', $o_configuration['package-name']);

        $o_configuration['domain'] = 'offsetset-domain';
        $o_configuration['package-name'] = 'offsetset-package-name';
        $this->assertEquals('offsetset-domain', $o_configuration['domain']);
        $this->assertEquals('offsetset-package-name', $o_configuration['package-name']);

        unset($o_configuration['domain']);
        $this->assertNull($o_configuration['domain']);
        $this->assertNull($o_configuration['confignotseted']);
    }

    public function testExportItemsWouldBeFine()
    {
        $path = '/etc/export.conf.php';
        $o_configuration = $this->getMockBuilder('Tox\\Application\\Configuration\\Configuration')
            ->setMethods(array('getPath'))
            ->disableOriginalConstructor()
            ->getMock();
        $o_configuration->expects($this->once())
            ->method('getPath')
            ->with($this->equalTo($path))
            ->will($this->returnValue(vfsStream::url($this->root . $path)));
        $o_configuration->import($path);

        $a_export = $o_configuration->export('ab*');
        $this->assertEquals($a_export, array('abw9j' => 'ababsdf2', 'ab2ee' => 'ab2ee'));

        $a_export = $o_configuration->export('sab*', array('default' => 'default'));
        $this->assertEquals($a_export, array('default' => 'default'));
    }

    public function testDumpWouldBeFine()
    {
        $path = '/etc/essential.conf.php';
        $import_path = '/etc/import.conf.php';
        $import_path_2 = '/etc/memcache.conf.php';

        $o_configuration = $this->getMockBuilder('Tox\\Application\\Configuration\\Configuration')
            ->setMethods(array('getPath'))
            ->disableOriginalConstructor()
            ->getMock();

        $o_configuration->expects($this->at(0))
            ->method('getPath')
            ->with($this->equalTo($path))
            ->will($this->returnValue(vfsStream::url($this->root . $path)));
        $o_configuration->expects($this->at(1))
            ->method('getPath')
            ->with($this->equalTo($import_path))
            ->will($this->returnValue(vfsStream::url($this->root . $import_path)));
        $o_configuration->expects($this->at(2))
            ->method('getPath')
            ->with($this->equalTo($import_path_2))
            ->will($this->returnValue(vfsStream::url($this->root . $import_path_2)));

        $o_configuration->__construct($path);
        $o_configuration->import($import_path);
        $o_configuration->import($import_path_2);

        $o_configuration->load(array('load-item' => 'loaded', 'load-item2' => 'loaded2'));
        $o_configuration->set('seted-item', 'seted');

        $o_dump = $o_configuration->dump();
        $this->assertTrue(array_key_exists('global', $o_dump));
        $this->assertTrue(array_key_exists('imported', $o_dump));
        $this->assertTrue(array_key_exists('loaded', $o_dump));
        $this->assertTrue(array_key_exists('seted', $o_dump));
    }

    public function testInvalidFileCanNotBeAssembled()
    {
        $o_configuration = $this->getMockBuilder('Tox\\Application\\Configuration\\Configuration')
            ->setMethods(array('abc'))
            ->disableOriginalConstructor()
            ->getMock();
        $this->assertFalse($o_configuration->getPath('/etc/conf.php'));
    }

    public function invalidConfigurationFile()
    {
        return array(
            array('/etc/db.conf.php'),
            array('/etc/var.conf.php'),
        );
    }

    public function legalConfigurationFile()
    {
        return array(
            array('/etc/constant.conf.php', json_encode(array('page-size' => 20, 'suffix' => 'apk'))),
            array('/etc/default.conf.php', json_encode(array('aaa' => 'value aaa', 'bbb' => 'value bbb'))),
            array(
                '/etc/essential.conf.php',
                json_encode(array('domain' => '9apps.mobi', 'package-name' => 'com.nineapps.android'))
            )
        );
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
