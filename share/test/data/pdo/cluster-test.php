<?php
/**
 * Defines the test case for Tox\Data\Pdo\Cluster.
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

namespace Tox\Data\Pdo;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/data/isource.php';
require_once __DIR__ . '/../../../../src/data/ipdo.php';
require_once __DIR__ . '/../../../../src/data/pdo/icluster.php';
require_once __DIR__ . '/../../../../src/data/pdo/cluster.php';

require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/data/pdo/clusteroopsexception.php';

require_once __DIR__ . '/../../../../src/data/pdo/pdo.php';
require_once __DIR__ . '/../../../../src/data/ipdostatement.php';

/**
 * Tests Tox\Data\Pdo\Cluster.
 *
 * @internal
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ClusterTest extends PHPUnit_Framework_TestCase
{
    protected $master;

    protected $slave;

    protected $cluster;

    protected function setUp()
    {
        $this->master = $this->getMock('Tox\\Data\\IPdo');
        $this->slave = $this->getMock('Tox\\Data\\IPdo');
        $this->slave->expects($this->any())->method('getId')
            ->will($this->returnValue(sha1('slave' . microtime())));
        $this->cluster = new Cluster($this->master);
        $this->cluster->addSlave($this->slave);
    }

    public function testGetInstanceManufactureMaster()
    {
        $s_dsn = microtime();
        $s_user = microtime();
        $s_pswd = microtime();
        $a_opts = array(microtime());
        $o_cluster = $this->getMock(
            'Tox\\Data\\Pdo\\Cluster',
            array('newCluster', 'newPdo'),
            array($this->slave),
            '',
            false
        );
        $o_cluster->staticExpects($this->once())->method('newPdo')
            ->with($this->equalTo($s_dsn), $this->equalTo($s_user), $this->equalTo($s_pswd), $this->equalTo($a_opts))
            ->will($this->returnValue($this->master));
        $o_cluster->staticExpects($this->once())->method('newCluster')
            ->with($this->equalTo($this->master));
        $o_cluster::getInstance($s_dsn, $s_user, $s_pswd, $a_opts);
    }

    public function testMagicMethods()
    {
        $o_cluster = $this->getMock(
            'Tox\\Data\\Pdo\\Cluster',
            array('getId', 'getDsn', 'getUsername'),
            array($this->slave),
            '',
            false
        );
        $o_cluster->expects($this->at(2))->method('getId');
        $o_cluster->expects($this->at(1))->method('getDsn');
        $o_cluster->expects($this->at(0))->method('getUsername');
        $o_cluster->username;
        $o_cluster->dsn;
        $o_cluster->id;
    }

    public function testCompulsiveMasterForGetId()
    {
        $s_ret = microtime();
        $this->master->expects($this->once())->method('getId')
            ->will($this->returnValue($s_ret));
        $this->assertEquals($s_ret, $this->cluster->getId());
    }

    public function testCompulsiveMasterForGetDsn()
    {
        $s_ret = microtime();
        $this->master->expects($this->once())->method('getDsn')
            ->will($this->returnValue($s_ret));
        $this->assertEquals($s_ret, $this->cluster->getDsn());
    }

    public function testCompulsiveMasterForGetUsername()
    {
        $s_ret = microtime();
        $this->master->expects($this->once())->method('getUsername')
            ->will($this->returnValue($s_ret));
        $this->assertEquals($s_ret, $this->cluster->getUsername());
    }

    public function testCompulsiveMasterForBeginTransaction()
    {
        $b_ret = (bool) rand(0, 1);
        $this->master->expects($this->once())->method('beginTransaction')
            ->will($this->returnValue($b_ret));
        $this->assertEquals($b_ret, $this->cluster->beginTransaction());
    }

    public function testCompulsiveMasterForCommit()
    {
        $b_ret = (bool) rand(0, 1);
        $this->master->expects($this->once())->method('commit')
            ->will($this->returnValue($b_ret));
        $this->assertEquals($b_ret, $this->cluster->commit());
    }

    public function testAnalysedPdoForExec()
    {
        $i_ret = rand();
        $s_sql = microtime();
        $this->master->expects($this->once())->method('exec')
            ->with($this->equalTo($s_sql))
            ->will($this->returnValue($i_ret));
        $this->assertEquals($i_ret, $this->cluster->exec($s_sql));
    }

    public function testAnalysedPdoForExec2()
    {
        $i_ret = rand();
        $s_sql = '  SeLeCT ' . microtime();
        $this->slave->expects($this->once())->method('exec')
            ->with($this->equalTo($s_sql))
            ->will($this->returnValue($i_ret));
        $this->assertEquals($i_ret, $this->cluster->exec($s_sql));
    }

    public function testCompulsiveMasterForGetAttribute()
    {
        $s_ret = microtime();
        $i_attr = rand();
        $this->master->expects($this->once())->method('getAttribute')
            ->will($this->returnValue($s_ret));
        $this->assertEquals($s_ret, $this->cluster->getAttribute($i_attr));
    }

    public function testPassedGetAvailableDrivers()
    {
        $this->assertEquals(\PDO::getAvailableDrivers(), Cluster::getAvailableDrivers());
    }

    public function testLocalStoredTransactionStatusForInTransaction()
    {
        $this->master->expects($this->never())->method('inTransaction');
        $this->slave->expects($this->never())->method('inTransaction');
        $this->assertFalse($this->cluster->inTransaction());
    }

    public function testCompulsiveMasterForLastInsertId()
    {
        $s_ret = microtime();
        $s_name = microtime();
        $this->master->expects($this->once())->method('lastInsertId')
            ->with($this->equalTo($s_name))
            ->will($this->returnValue($s_ret));
        $this->assertEquals($s_ret, $this->cluster->lastInsertId($s_name));
    }

    public function testAnalysedPdoForPrepare()
    {
        $i_ret = rand();
        $s_sql = microtime();
        $a_opts = array(microtime(true));
        $this->master->expects($this->once())->method('prepare')
            ->with($this->equalTo($s_sql), $this->equalTo($a_opts))
            ->will($this->returnValue($i_ret));
        $this->assertEquals($i_ret, $this->cluster->prepare($s_sql, $a_opts));
    }

    public function testAnalysedPdoForPrepare2()
    {
        $i_ret = rand();
        $s_sql = 'sElEct                        ' . microtime();
        $a_opts = array(microtime(true));
        $this->slave->expects($this->once())->method('prepare')
            ->with($this->equalTo($s_sql), $this->equalTo($a_opts))
            ->will($this->returnValue($i_ret));
        $this->assertEquals($i_ret, $this->cluster->prepare($s_sql, $a_opts));
    }

    public function testAnalysedPdoForQuery()
    {
        $i_ret = rand();
        $s_sql = 'SELECT' . microtime();
        $this->master->expects($this->once())->method('query')
            ->with($this->equalTo($s_sql))
            ->will($this->returnValue($i_ret));
        $this->assertEquals($i_ret, $this->cluster->query($s_sql));
    }

    public function testAnalysedPdoForQuery2()
    {
        $i_ret = rand();
        $s_sql = ' select ' . microtime();
        $this->slave->expects($this->once())->method('query')
            ->with($this->equalTo($s_sql))
            ->will($this->returnValue($i_ret));
        $this->assertEquals($i_ret, $this->cluster->query($s_sql));
    }

    /**
     * @expectedException Tox\Data\Pdo\ClusterOopsException
     */
    public function testForbiddenRealize()
    {
        $this->master->expects($this->never())->method('realize');
        $this->slave->expects($this->never())->method('realize');
        $this->cluster->realize($this->getMock('Tox\\Data\\IPdoStatement'));
    }

    public function testCompulsiveMasterForQuote()
    {
        $s_ret = microtime();
        $s_lob = microtime();
        $this->master->expects($this->once())->method('quote')
            ->with($this->equalTo($s_lob))
            ->will($this->returnValue($s_ret));
        $this->assertEquals($s_ret, $this->cluster->quote($s_lob));
    }

    public function testCompulsiveMasterForRollBack()
    {
        $b_ret = (bool) rand(0, 1);
        $this->master->expects($this->once())->method('rollBack')
            ->will($this->returnValue($b_ret));
        $this->assertEquals($b_ret, $this->cluster->rollBack());
    }

    public function testLoopOverPdosForSetAttribute()
    {
        $b_ret = (bool) rand(0, 1);
        $b_ret2 = !$b_ret;
        $i_attr = rand();
        $s_val = microtime();
        $this->master->expects($this->once())->method('setAttribute')
            ->with($this->equalTo($i_attr), $this->equalTo($s_val))
            ->will($this->returnValue($b_ret));
        $this->slave->expects($this->once())->method('setAttribute')
            ->with($this->equalTo($i_attr), $this->equalTo($s_val))
            ->will($this->returnValue($b_ret2));
        $this->assertEquals($b_ret, $this->cluster->setAttribute($i_attr, $s_val));
    }

    /**
     * @depends testAnalysedPdoForExec
     * @depends testAnalysedPdoForExec2
     */
    public function testMasterUsedWhileNoSlaves()
    {
        $o_cluster = new Cluster($this->slave);
        $i_ret = rand();
        $s_sql = '  SeLeCT ' . microtime();
        $this->slave->expects($this->once())->method('exec')
            ->with($this->equalTo($s_sql))
            ->will($this->returnValue($i_ret));
        $this->assertEquals($i_ret, $o_cluster->exec($s_sql));
    }

    /**
     * @depends testAnalysedPdoForExec
     * @depends testAnalysedPdoForExec2
     * @dataProvider provideExecCases
     */
    public function testOverflowWeight($sql, $ret)
    {
        $s_sql = str_repeat(' ', rand(0, 10)) .
            (rand(0, 10) > 5 ? 'S' : 's') .
            (rand(0, 10) > 5 ? 'E' : 'e') .
            (rand(0, 10) > 5 ? 'L' : 'l') .
            (rand(0, 10) > 5 ? 'E' : 'e') .
            (rand(0, 10) > 5 ? 'C' : 'c') .
            (rand(0, 10) > 5 ? 'T' : 't') .
            str_repeat(' ', rand(1, 10)) . $sql;
        $o_slave2 = $this->getMock('Tox\\Data\\IPdo');
        $o_slave2->expects($this->any())->method('getId')
            ->will($this->returnValue(sha1('slave2' . microtime())));
        $this->cluster->addSlave($o_slave2, 1);
        $this->slave->expects($this->never())->method('exec');
        $o_slave2->expects($this->once())->method('exec')
            ->with($this->equalTo($s_sql))
            ->will($this->returnValue($ret));
        $this->cluster->exec($s_sql);
    }

    public function provideExecCases()
    {
        return array(
            array(microtime(), microtime(true)),
            array(microtime(), microtime(true)),
            array(microtime(), microtime(true)),
            array(microtime(), microtime(true)),
            array(microtime(), microtime(true)),
            array(microtime(), microtime(true)),
            array(microtime(), microtime(true)),
            array(microtime(), microtime(true)),
            array(microtime(), microtime(true)),
            array(microtime(), microtime(true))
        );
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
