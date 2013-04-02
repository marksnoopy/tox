<?php
/**
 * Defines the test case for Tox\Data\Kv\kv.
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

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/data/isource.php';
require_once __DIR__ . '/../../../../src/data/ikv.php';
require_once __DIR__ . '/../../../../src/data/kv/kv.php';

use Tox\Data\KV;
use Tox;

/**
 * Tests Tox\Data\KV.
 *
 * @internal
 *
 * @package tox.data.kv
 * @author  Qiang Fu <fuqiang007enter@gmail.com>
 */
class KvTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider dataProvider
     */
    public function testSetting($key, $value, $expire, $prefixString, $expectResult, $result) {
        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                //     ->disableOriginalConstructor()
                ->setMethods(array('setValue'))
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->once())
                ->method('setValue')
                ->with(
                        $this->equalTo(md5($prefixString . $key)), $this->equalTo(serialize(array($value))), $this->equalTo($expire)
                )->will($this->returnValue($expectResult));

        $this->assertEquals($result, $o_mockKv->set($key, $value, $expire));
    }

    public function testIncrease() {
        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->any())
                ->method('increase')
                ->with($this->equalTo('aaa'))
                ->will($this->returnValue(false));

        $this->assertFalse($o_mockKv->increase('aaa'));
    }

    public function testDecrease() {
        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->any())
                ->method('decrease')
                ->with($this->equalTo('aaa'))
                ->will($this->returnValue(false));

        $this->assertFalse($o_mockKv->decrease('aaa'));
    }

    public function testPush() {
        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->any())
                ->method('push')
                ->with($this->equalTo('aaa'))
                ->will($this->returnValue(false));

        $this->assertFalse($o_mockKv->push('aaa'));
    }

    public function testPop() {
        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->any())
                ->method('pop')
                ->with($this->equalTo('aaa'))
                ->will($this->returnValue(false));

        $this->assertFalse($o_mockKv->pop('aaa'));
    }

    public function testShift() {
        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->any())
                ->method('shift')
                ->with($this->equalTo('aaa'))
                ->will($this->returnValue(false));

        $this->assertFalse($o_mockKv->shift('aaa'));
    }

    public function testUnShift() {
        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->any())
                ->method('unshift')
                ->with($this->equalTo('aaa'))
                ->will($this->returnValue(false));

        $this->assertFalse($o_mockKv->unshift('aaa'));
    }

    /**
     * @dataProvider dataProvider
     * @depends testSetting
     */
    public function testGetting($key, $value, $expire, $prefixString, $expectResult, $result) {

        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->setMethods(array('getValue'))
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->once())
                ->method('getValue')
                ->with($this->equalTo(md5($prefixString . $key)))
                ->will($this->returnValue(serialize(array($value))));

        $this->assertEquals($value, $o_mockKv->get($key));
    }

    /**
     * @dataProvider dataProvider
     * @depends testSetting
     */
    public function testGetting2($key, $value, $expire, $prefixString, $expectResult, $result) {

        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->setMethods(array('getValue'))
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->once())
                ->method('getValue')
                ->with($this->equalTo(md5($prefixString . $key)))
                ->will($this->returnValue(serialize('sss')));

        $this->assertEquals(false, $o_mockKv->get($key));
    }

    /**
     * @dataProvider dataProvider
     * @depends testSetting
     */
    public function testGetting3($key, $value, $expire, $prefixString, $expectResult, $result) {

        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->setMethods(array('getValue'))
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->once())
                ->method('getValue')
                ->with($this->equalTo(md5($prefixString . $key)))
                ->will($this->returnValue(false));

        $this->assertEquals(false, $o_mockKv->get($key));
    }

    public function testClear() {
        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->setMethods(array('clearValues'))
                ->getMockForAbstractClass();

        $o_mockKv->expects($this->once())
                ->method('clearValues')
                ->will($this->returnValue(false));

        $this->assertEquals(false, $o_mockKv->clear());
    }

    public function dataProvider() {
        return array(
            array('key1', 'sss', 500, 'memcached', true, true),
            array('key2', 'valuetest', 500, 'memcached', true, true),
            array('key3', 'valuetest22', 500, 'memcached', false, false),
        );
    }

    public function testConstruct() {
        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();

        //   $o_mockKv->__construct();

        $this->assertEquals('memcached', $o_mockKv->keyPrefix);
    }

    /**
     * @dataProvider dataProviderOffset
     */
    public function testoffsetSet($key, $value, $expire, $prefixString, $expectResult) {
        $o_mockKv = new Subkv();

        $o_mockKv[$key] = $value;
        $this->assertEquals($value, $o_mockKv[$key]);
    }

    /**
     * @dataProvider dataProviderOffset
     */
    public function testoffsetExists($key, $value, $expire, $prefixString, $expectResult) {
        $o_mockKv = new Subkv();

        $o_mockKv[$key] = $value;
        $this->assertEquals(true, isset($o_mockKv[$key]));
    }

    /**
     * @dataProvider dataProviderOffset
     */
    public function testoffsetSet2($key, $value, $expire, $prefixString, $expectResult) {

        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();
        $o_mockKv->expects($this->any())
                ->method('setValue')
                ->with(
                        $this->equalTo(md5($prefixString . $key)), $this->equalTo(serialize(array($value))), $this->equalTo($expire)
        );

        $o_mockKv[$key] = $value;
    }

    /**
     * @dataProvider dataProviderOffset
     */
    public function testoffsetGet($key, $value, $expire, $prefixString, $expectResult) {

        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();
        $o_mockKv->expects($this->once())
                ->method('getValue')
                ->with($this->equalTo(md5($prefixString . $key)))
                ->will($this->returnValue(serialize(array($value))));
        $this->assertEquals($value, $o_mockKv[$key]);
    }

    /**
     * @dataProvider dataProviderOffset
     */
    public function testoffsetUnset($key, $value, $expire, $prefixString, $expectResult) {

        $o_mockKv = $this->getMockBuilder('Tox\\Data\\Kv\\kv')
                ->getMockForAbstractClass();
        $o_mockKv->expects($this->once())
                ->method('deleteValue')
                ->with($this->equalTo(md5($prefixString . $key)))
                ->will($this->returnValue(true));
        unset($o_mockKv[$key]);
    }

    public function dataProviderOffset() {
        return array(
            array('key1', 'sss', 0, 'memcached', true),
            array('key2', 'valuetest', 0, 'memcached', true),
            array('key3', 'valuetest22', 0, 'memcached', true),
        );
    }

}

class Subkv extends KV\KV {

    private $container = array();

    protected function getValue($key) {
        return isset($this->container[$key]) ? $this->container[$key] : null;
    }

    protected function setValue($key, $val, $expire = 0) {
        if (is_null($key)) {
            $this->container[] = $val;
        } else {
            $this->container[$key] = $val;
        }
    }

    protected function deleteValue($key) {
        unset($this->container[$key]);
    }

    protected function clearValues() {
        unset($this->container);
    }

}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
