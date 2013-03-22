<?php
/**
 * Defines the test case for Tox\Data\Kv\Memcache.
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

namespace ToxTest\Data\Kv;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../../src/tox/core/assembly.php';
require_once __DIR__ . '/../../../../../src/tox/data/isource.php';
require_once __DIR__ . '/../../../../../src/tox/data/ikv.php';
require_once __DIR__ . '/../../../../../src/tox/data/kv/kv.php';
require_once __DIR__ . '/../../../../../src/tox/data/kv/memcacheserverconfiguration.php';
require_once __DIR__ . '/../../../../../src/tox/data/kv/memcache.php';

use Tox\Data\KV;
use Tox;

/**
 * Tests Tox\Data\KV.
 *
 * @internal
 *
 * @package tox.data.kv
 * @author  Qiang Fu <fuqiang@ucweb.com>
 */
class MemcacheTest extends PHPUnit_Framework_TestCase
{
    protected $o_mem;

    protected function setUp()
    {
        $this->o_mem = new Tox\Data\Kv\Memcache();
    }

    /**
     * @dataProvider configProvide
     */
    public function testServerConfigset($config, $host1, $port1, $host2, $port2)
    {
        $this->o_mem->setServers($config);

        $a_config = $this->o_mem->getServers();

        $this->assertEquals($host1, $a_config[0]->host);
        $this->assertEquals($port1, $a_config[0]->port);
        $this->assertEquals($host2, $a_config[1]->host);
        $this->assertEquals($port2, $a_config[1]->port);
    }


    /**
     * @dataProvider configProvideSetValue
     * @depends testServerConfigset
     */
    public function testSetValue($config, $key, $val, $expire)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();
        if ($expire > 0) {
            $o_mockMemcached->Expects($this->once())
                    ->method('set')
                    ->with($this->equalTo($key), $this->equalTo($val), $this->equalTo($expire + time()))
                    ->will($this->returnValue(True));
        } else {
            $o_mockMemcached->Expects($this->once())
                    ->method('set')
                    ->with($this->equalTo($key), $this->equalTo($val), $this->equalTo(300))
                    ->will($this->returnValue(True));
        }
        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $o_mem2->setExpireTime(300);
        $this->assertTrue($o_mem2->setValue($key, $val, $expire));
    }

    /**
     * @dataProvider configProvideSetValue2
     * @depends testServerConfigset
     */
    public function testSetValue2($config, $key, $val, $expire)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();

        $o_mockMemcached->Expects($this->once())
                ->method('set')
                ->with($this->equalTo($key), $this->equalTo($val), $this->equalTo(0))
                ->will($this->returnValue(True));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->assertTrue($o_mem2->setValue($key, $val, $expire));
    }

    public function configProvideSetValue()
    {
        return array(
            array(array(//是否使用memcached
                    'useMemcached' => true,
                    // memcache相关配置。
                    'memcache' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                    )
                ), 'key', 'ssss', 500),
            array(array(//是否使用memcached
                    'useMemcached' => false,
                    // memcache相关配置。
                    'memcache' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                    )
                ), 'key', 'ssss', 0),
        );
    }

    public function configProvideSetValue2()
    {
        return array(
            array(array(//是否使用memcached
                    'useMemcached' => false,
                    // memcache相关配置。
                    'memcache' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                    )
                ), 'key', 'ssss', 0),
        );
    }

    /**
     * @dataProvider configProvide
     * @depends testServerConfigset
     */
    public function testGetValue($config)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();
        $o_mockMemcached->Expects($this->once())
                ->method('get')
                ->with($this->equalTo('key'))
                ->will($this->returnValue(True));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->assertTrue($o_mem2->getValue('key'));
    }

    /**
     * @dataProvider configProvide
     * @depends testServerConfigset
     */
    public function testDeleteValue($config)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();
        $o_mockMemcached->Expects($this->once())
                ->method('delete')
                ->with($this->equalTo('key'), $this->equalTo(0))
                ->will($this->returnValue(True));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->assertTrue($o_mem2->deleteValue('key'));
    }

    /**
     * @dataProvider configProvide
     * @depends testServerConfigset
     */
    public function testGetValues($config)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();
        $o_mockMemcached->Expects($this->once())
                ->method('getMulti')
                ->with($this->equalTo(array('key')))
                ->will($this->returnValue(True));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->assertTrue($o_mem2->getValues(array('key')));
    }

    /**
     * @dataProvider configProvide
     * @depends testServerConfigset
     */
    public function testClearValue($config)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();
        $o_mockMemcached->Expects($this->once())
                ->method('flush')
                ->will($this->returnValue(True));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->assertTrue($o_mem2->clearValues());
    }

    /**
     * @dataProvider configProvide
     * @depends testServerConfigset
     */
    public function testAddValue($config)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();
        $o_mockMemcached->Expects($this->once())
                ->method('add')
                ->with($this->equalTo('key'), $this->equalTo('sss'), $this->equalTo(0))
                ->will($this->returnValue(True));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->assertTrue($o_mem2->addValue('key', 'sss', 0));
    }

    public function testConstruct()
    {
        $this->assertTrue($this->o_mem->useMemcached);
    }

    /**
     * @covers Tox\Data\Kv\Memcache::setExpireTime
     * @covers Tox\Data\Kv\Memcache::getExpireTime
     */
    public function testExpireTime()
    {
        $this->o_mem->setExpireTime(555);
        $this->assertEquals(555, $this->o_mem->getExpireTime());
    }

    public function configProvideExpireTime()
    {
        return array(
            array('key', 'ssss', 500, 500),
            array('key', 'ssss', 0, 0),
        );
    }

    public function configProvide()
    {
        return array(
            array(
                array(
                    //是否使用memcached
                    'useMemcached' => true,
                    // memcache相关配置。
                    'memcache' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11212',
                        ),
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11212',
                        ),
                    )
                ),
                '127.0.0.1',
                '11211',
                '127.0.0.1',
                '11212',
            ),
            array(
                array(
                    //是否使用memcached
                    'useMemcached' => false,
                    // memcache相关配置。
                    'memcache' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11212',
                        ),
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                        ),
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11212',
                        ),
                    )
                ),
                '127.0.0.1',
                '11211',
                '127.0.0.1',
                '11212',
            )
        );
    }

}

class SubMem extends KV\Memcache
{
    private $container = array();

    public function getValue($key)
    {
        return parent::getValue($key);
    }

    public function getValues($keys)
    {
        return parent::getValues($keys);
    }

    public function setValue($key, $val, $expire = 0)
    {
        return parent::setValue($key, $val, $expire);
    }

    public function addValue($key, $val, $expire = 0)
    {
        return parent::addValue($key, $val, $expire);
    }

    public function deleteValue($key)
    {
        return parent::deleteValue($key);
    }

    public function clearValues()
    {
        return parent::clearValues();
    }

}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120