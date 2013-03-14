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
require_once __DIR__ . '/../../../../../src/tox/data/ikv.php';
require_once __DIR__ . '/../../../../../src/tox/data/kv/kv.php';
require_once __DIR__ . '/../../../../../src/tox/data/kv/memcacheserverconifguration.php';
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
class MemcacheTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider configProvide
     */
    public function testServerConfigset($config) {
        $o_memcache = new Tox\Data\Kv\Memcache();
        $o_memcache->setServers($config);

        $a_config = $o_memcache->getServers();
        $this->assertEquals('127.0.0.1', $a_config[0]->host);
        $this->assertEquals('11211', $a_config[0]->port);
        $this->assertEquals('soft', $a_config[0]->type);
    }

    /**
     * @dataProvider configProvide
     * @depends testServerConfigset
     */
    public function testGet($config) {
        $mem = new Tox\Data\Kv\Memcache();

        $mem->setServers($config);
        $mem->init();

        $s_key = 't';
        $mem->set($s_key, 'sss', 500);
        $this->assertEquals('sss', $mem->get($s_key));
    }

    public function configProvide() {
        return array(
            array(
                array(
                    //是否使用memcached
                    'useMemcached' => true,
                    // memcache相关配置。
                    'memcache' => array(
                        'host' => '127.0.0.1',
                        'port' => '11211',
                    ),
                    // memcached相关配置。
                    'memcached' => array(
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                            'type' => 'soft',
                        ),
                        array(
                            'host' => '127.0.0.1',
                            'port' => '11211',
                            'type' => 'game',
                        ),
                    )
                )
            )
        );
    }

}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120