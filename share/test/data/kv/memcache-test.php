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
 * @copyright © 2012-2013 PHP-Tox.org
 * @license   GNU General Public License, version 3
 */

namespace ToxTest\Data\Kv;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/data/isource.php';
require_once __DIR__ . '/../../../../src/data/ikv.php';
require_once __DIR__ . '/../../../../src/data/kv/kv.php';
require_once __DIR__ . '/../../../../src/data/kv/memcacheserverconfiguration.php';



require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/data/kv/@exception/memcachevaluenotstring.php';
require_once __DIR__ . '/../../../../src/data/kv/@exception/memcachekeytoolong.php';
require_once __DIR__ . '/../../../../src/data/kv/@exception/emptyhost.php';
require_once __DIR__ . '/../../../../src/data/kv/@exception/memcacheconfignotarray.php';
require_once __DIR__ . '/../../../../src/data/kv/memcache.php';

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
     * @dataProvider dataProvideinit
     */
    public function testinit($config, $compressionstat, $field)
    {


        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();
        $o_mockMemcached->Expects($this->any())
                ->method('getServerList')
                ->will(
                    $this->returnValue(
                        array(
                            array(
                                'host' => '127.0.0.1',
                                'port' => '11212',
                                'field' => 'data'
                            )
                        )
                    )
                );

        $o_mem2 = $this->getMockBuilder('Tox\\Data\\Kv\\Memcache')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->any())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);

        $o_mem2->setCompression($compressionstat);
        $o_mem2->__construct($field);
        $o_mem2->init();
    }

    /**
     * @dataProvider dataProvideinit2
     * @expectedException  Tox\Data\Kv\MemcacheConfigNotArrayException
     */
    public function testinitWithServerConfigIsError($config, $compressionstat, $field)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();

        $o_mem2 = $this->getMockBuilder('Tox\\Data\\Kv\\Memcache')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->any())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);

        $o_mem2->setCompression($compressionstat);
        $o_mem2->__construct($field);
        $o_mem2->init();
    }

    /**
     * @dataProvider dataProvideinit3
     */
    public function testinitMemcache($config, $compressionstat, $field)
    {
        $o_mockMemcache = $this->getMockBuilder('Memcache')
                ->setMethods(array('addServer'))
                ->getMock();
        $o_mockMemcache->Expects($this->any())
                ->method('addServer')
                ->will(
                    $this->returnValue(
                        true
                    )
                );

        $o_mem2 = $this->getMockBuilder('Tox\\Data\\Kv\\Memcache')
                ->setMethods(array('defaultMemcache'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->any())
                ->method('defaultMemcache')
                ->will($this->returnValue($o_mockMemcache));
        $o_mem2->setServers($config);

        $o_mem2->__construct($field);
        $o_mem2->init();
    }

    public function dataProvideinit()
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
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                            'field' => 'page',
                        ),
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11212',
                            'field' => 'data',
                        ),
                    )
                ),
                true,
                'page',
            ),
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
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                            'field' => 'page',
                        ),
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11212',
                            'field' => 'data',
                        ),
                    )
                ),
                false,
                null,
            ),
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
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                            'field' => 'page',
                        ),
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11212',
                            'field' => 'data',
                        ),
                    )
                ),
                false,
                'data',
            ),
        );
    }

    public function dataProvideinit2()
    {
        return array(
            array(
                array(
                    //是否使用memcached
                    'useMemcached' => true,
                    // memcache相关配置。
                    'memcache' => array(
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                    )
                ),
                false,
                'data',
            ),
        );
    }

    public function dataProvideinit3()
    {
        return array(
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
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                            'field' => 'page',
                        ),
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11212',
                            'field' => 'data',
                        ),
                    )
                ),
                true,
                null,
            ),
        );
    }

    /**
     * @dataProvider dataProvideDefaultMemcached
     */
    public function testDefaultMemcached($config, $compressionstat, $field)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();


        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->getMockForAbstractClass();

        $o_mem2->setServers($config);
        $o_mem2->__construct($field);
        $o_mem2->setCompression($compressionstat);
        $z = $o_mem2->defaultMemcached();
        //   var_dump($z);die;
        $this->assertTrue(true);
    }

    public function dataProvideDefaultMemcached()
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
                    ),
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                            'field' => 'data',
                        ),
                    )
                ),
                true,
                'data'
            ),
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
                    ),
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                            'field' => 'data',
                        ),
                    )
                ),
                true,
                'data'
            ),
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
                    ),
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                            'field' => 'data',
                        ),
                    )
                ),
                false,
                'page',
            ),
        );
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
                    ->with($this->equalTo($key), $this->equalTo($val), $this->equalTo($expire))
                    ->will($this->returnValue(true));
        } else {
            $o_mockMemcached->Expects($this->once())
                    ->method('set')
                    ->with($this->equalTo($key), $this->equalTo($val), $this->equalTo(300))
                    ->will($this->returnValue(true));
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
        $this->asserttrue($o_mem2->setValue($key, $val, $expire));
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
                ->with($this->equalTo($key), $this->equalTo($val), $this->equalTo($expire))
                ->will($this->returnValue(true));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->setValue($key, $val, $expire));
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
                ), 'key', 'ssss', 0),
        );
    }

    public function configProvideSetValue2()
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
                ), 'key', 'ssss', 0),
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
                ->will($this->returnValue(true));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->getValue('key'));
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
                ->will($this->returnValue(true));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->deleteValue('key'));
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
                ->will($this->returnValue(true));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->getValues(array('key')));
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
                ->will($this->returnValue(true));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->clearValues());
    }

    /**
     * @dataProvider configProvideSetValue2
     * @depends testServerConfigset
     */
    public function testAddValue($config, $key, $val, $expire)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();
        $o_mockMemcached->Expects($this->once())
                ->method('add')
                ->with($this->equalTo($key), $this->equalTo($val), $this->lessThanOrEqual($expire + 10 + time()))
                ->will($this->returnValue(true));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->addValue($key, $val, $expire));
    }

    public function testConstruct()
    {
        $o_mem2 = $this->getMockBuilder('Tox\\Data\\Kv\\memcache')
                ->setMethods(array('__construct'))
                ->getMockForAbstractClass();
        $o_mem2->Expects($this->any())
                ->method('__construct')
                ->with($this->equalTo('field'))
                ->will($this->returnValue(true));

        $this->assertEquals(null, $o_mem2->__construct('field'));
    }

    public function testConstruct2()
    {
        $this->asserttrue($this->o_mem->useMemcached);
    }

    public function testSetCompression()
    {
        $this->o_mem->setCompression(false);

        $this->assertFalse($this->o_mem->getCompression());
        $this->o_mem->setCompression(true);

        $this->assertTrue($this->o_mem->getCompression());
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

    /**
     * @dataProvider configProvideSetValue2
     * @depends testServerConfigset
     */
    public function testSetNginxValue($config, $key, $val, $expire)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();

        $o_mockMemcached->Expects($this->once())
                ->method('set')
                ->with($this->equalTo($key), $this->equalTo($val), $this->lessThanOrEqual($expire + time()))
                ->will($this->returnValue(true));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->setNginxMemcacheValue($key, $val, $expire));
    }

    /**
     * @dataProvider configProvideSetNginxValue

     * @expectedException Tox\Data\Kv\MemcacheValueNotStringException
     */
    public function testSetNginxValue3($config, $key, $val, $expire)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->setNginxMemcacheValue($key, $val, $expire));
    }

    /**
     * @dataProvider configProvideSetNginxValue2
     * @expectedException   Tox\Data\Kv\MemcacheKeyTooLongException
     */
    public function testSetNginxValue2($config, $key, $val, $expire)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->setNginxMemcacheValue($key, $val, $expire));
    }

    public function configProvideSetNginxValue()
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
                ), 'key', array(), 0),
        );
    }

    public function configProvideSetNginxValue2()
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
                ),
                'keysdjfoasjdfioasd;fncv;zjoidfj;ajfijfs;kjbiasjiui3ij;fladfj;kja
                    soifu[apjf ;jdfoijaugfij;ajgodiuf;ajkfjdfjaoi;jefkdjasp;ifjaoijkljafkldjoaisfje
                    ;lija;dkjfkja;iej;fkjdaksjfi;ldaifje;kj;lsjkdf
                    jijuiag;kjadksfjiej;asljfkdjskljdfkdajsfkljasijef;jdakfjkljadfl;jakfjdl',
                'ssss', 500),
        );
    }

    /**
     * @dataProvider configProvide
     * @depends testServerConfigset
     */
    public function testGetNginxValue($config)
    {
        $o_mockMemcached = $this->getMockBuilder('Memcached')
                ->getMock();
        $o_mockMemcached->Expects($this->once())
                ->method('get')
                ->with($this->equalTo('key'))
                ->will($this->returnValue(true));

        $o_mem2 = $this->getMockBuilder('ToxTest\\Data\\Kv\\SubMem')
                ->setMethods(array('defaultMemcached'))
                ->getMockForAbstractClass();

        $o_mem2->Expects($this->once())
                ->method('defaultMemcached')
                ->will($this->returnValue($o_mockMemcached));
        $o_mem2->setServers($config);
        $o_mem2->init();

        $this->asserttrue($o_mem2->getNginxMemcacheValue('key'));
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

    public function defaultMemcache()
    {
        return parent::defaultMemcache();
    }

    public function defaultMemcached()
    {
        return parent::defaultMemcached();
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
