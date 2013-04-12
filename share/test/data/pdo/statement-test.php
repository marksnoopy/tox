<?php
/**
 * Defines the test case for Tox\Data\Pdo\Statement.
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
require_once __DIR__ . '/../../../../src/data/ipdostatement.php';
require_once __DIR__ . '/../../../../src/data/pdo/statement.php';

require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/data/pdo/closedstatementexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/executingfailureexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/columnbindingfailureexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/parambindingfailureexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/valuebindingfailureexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/cursorclosingfailureexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/rowsetiteratingfailureexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/attributesettingfailureexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/fetchmodesettingfailureexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/executedstatementexception.php';

require_once __DIR__ . '/../../../../src/data/isource.php';
require_once __DIR__ . '/../../../../src/data/ipdo.php';

use Tox\Data;

use Exception;

/**
 * Tests Tox\Data\Pdo\Statement.
 *
 * @internal
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class StatementTest extends PHPUnit_Framework_TestCase
{
    /**
     * Stores the mocking data object for testing.
     *
     * @var Data\IPdo
     */
    protected $pdo;

    protected function setUp()
    {
        $this->pdo = $this->getMock('Tox\\Data\\IPdo');
    }

    public function testStringCasting()
    {
        $s_sql = microtime();
        $o_stmt = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, $s_sql)
        );
        $this->assertEquals($s_sql, (string) $o_stmt);
    }

    public function testSha1AlgorithmUsedForId()
    {
        $o_stmt = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertRegExp('@^[\\da-z]{40}$@i', $o_stmt->getId());
    }

    public function testRetrievingPdo()
    {
        $o_stmt = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertSame($this->pdo, $o_stmt->getPdo());
    }

    public function testRetrievingType()
    {
        $o_stmt = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertNull($o_stmt->getType());
    }

    public function testMagicMethods()
    {
        $s_value = microtime();
        $o_stmt = $this->getMock(
            'Tox\\Data\\Pdo\\Statement',
            array('getId', 'getType', 'getPdo'),
            array(),
            '',
            false
        );
        $o_stmt->expects($this->once())->method('getId')
            ->will($this->returnValue($s_value));
        $o_stmt->expects($this->once())->method('getType')
            ->will($this->returnValue($s_value));
        $o_stmt->expects($this->once())->method('getPdo')
            ->will($this->returnValue($s_value));
        $this->assertEquals($s_value, $o_stmt->id);
        $this->assertEquals($s_value, $o_stmt->type);
        $this->assertEquals($s_value, $o_stmt->pdo);
    }

    public function testLazyLoadingForBindColumn()
    {
        $i_key = rand();
        $s_val1 = microtime();
        $i_val2 = rand();
        $i_val3 = rand();
        $f_val4 = 4 + microtime(true);
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('bindColumn')
            ->with(
                $this->equalTo($i_key),
                $this->equalTo($s_val1),
                $this->equalTo($i_val2),
                $this->equalTo($i_val3),
                $this->equalTo($f_val4)
            )->will($this->returnValue(microtime()));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertSame($o_stmt2, $o_stmt2->bindColumn($i_key, $s_val1, $i_val2, $i_val3, $f_val4));
    }

    public function testLazyLoadingForBindParam()
    {
        $i_key = rand();
        $s_val1 = microtime();
        $i_val2 = rand();
        $i_val3 = rand();
        $f_val4 = 4 + microtime(true);
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('bindParam')
            ->with(
                $this->equalTo($i_key),
                $this->equalTo($s_val1),
                $this->equalTo($i_val2),
                $this->equalTo($i_val3),
                $this->equalTo($f_val4)
            )->will($this->returnValue(microtime()));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertSame($o_stmt2, $o_stmt2->bindParam($i_key, $s_val1, $i_val2, $i_val3, $f_val4));
    }

    public function testLazyLoadingForBindValue()
    {
        $this->pdo->expects($this->never())->method('realize');
        $o_stmt = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertSame($o_stmt, $o_stmt->bindValue(microtime(), microtime()));
    }

    public function testLazyLoadingForCloseCursor()
    {
        $this->pdo->expects($this->never())->method('realize');
        $o_stmt = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt->closeCursor();
    }

    public function testLazyLoadingForColumnCount()
    {
        $m_ret = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('columnCount')
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertEquals($m_ret, $o_stmt2->columnCount());
    }

    public function testLazyLoadingForDebugDumpParams()
    {
        $m_ret = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('debugDumpParams')
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertEquals($m_ret, $o_stmt2->debugDumpParams());
    }

    public function testLazyLoadingForExecute()
    {
        $m_ret = microtime();
        $a_pars = array(microtime());
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('execute')
            ->with($this->equalTo($a_pars))
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertSame($o_stmt2, $o_stmt2->execute($a_pars));
    }

    public function testLazyLoadingForFetch()
    {
        $m_ret = microtime();
        $m_val1 = microtime();
        $m_val2 = microtime();
        $m_val3 = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('fetch')
            ->with($this->equalTo($m_val1), $this->equalTo($m_val2), $this->equalTo($m_val3))
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertEquals($m_ret, $o_stmt2->fetch($m_val1, $m_val2, $m_val3));
    }

    public function testLazyLoadingForFetchAll()
    {
        $m_ret = microtime();
        $m_val1 = microtime();
        $m_val2 = microtime();
        $m_val3 = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('fetchAll')
            ->with($this->equalTo($m_val1), $this->equalTo($m_val2), $this->equalTo($m_val3))
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertEquals($m_ret, $o_stmt2->fetchAll($m_val1, $m_val2, $m_val3));
    }

    public function testLazyLoadingForFetchColumn()
    {
        $m_ret = microtime();
        $m_val1 = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('fetchColumn')
            ->with($this->equalTo($m_val1))
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertEquals($m_ret, $o_stmt2->fetchColumn($m_val1));
    }

    public function testLazyLoadingForFetchObject()
    {
        $m_ret = microtime();
        $m_val1 = microtime();
        $m_val2 = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('fetchObject')
            ->with($this->equalTo($m_val1), $this->equalTo($m_val2))
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertEquals($m_ret, $o_stmt2->fetchObject($m_val1, $m_val2));
    }

    public function testLazyLoadingForGetAttribute()
    {
        $m_ret = microtime();
        $m_val1 = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('getAttribute')
            ->with($this->equalTo(intval($m_val1)))
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertEquals($m_ret, $o_stmt2->getAttribute($m_val1));
    }

    public function testLazyLoadingForGetColumnMeta()
    {
        $m_ret = microtime();
        $m_val1 = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('getColumnMeta')
            ->with($this->equalTo($m_val1))
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertEquals($m_ret, $o_stmt2->getColumnMeta($m_val1));
    }

    public function testLazyLoadingForNextRowset()
    {
        $m_ret = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('nextRowset')
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertSame($o_stmt2, $o_stmt2->nextRowset());
    }

    public function testLazyLoadingForRowCount()
    {
        $m_ret = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('rowCount')
            ->will($this->returnValue($m_ret));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertEquals($m_ret, $o_stmt2->rowCount());
    }

    public function testLazyLoadingForSetAttribute()
    {
        $this->pdo->expects($this->never())->method('realize');
        $o_stmt = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertSame($o_stmt, $o_stmt->setAttribute(microtime(), microtime()));
    }

    public function testLazyLoadingForSetFetchMode()
    {
        $this->pdo->expects($this->never())->method('realize');
        $o_stmt = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertSame($o_stmt, $o_stmt->setFetchMode(microtime()));
    }

    /**
     * @depends testLazyLoadingForGetAttribute
     * @depends testLazyLoadingForSetAttribute
     */
    public function testLazyLoadingForGettingSetAttribute()
    {
        $i_attr = rand();
        $s_value = microtime();
        $o_stmt = $this->getMock(
            'Tox\\Data\\Pdo\\Statement',
            array('realize'),
            array($this->pdo, microtime())
        );
        $o_stmt->expects($this->never())->method('realize');
        $this->assertEquals($s_value, $o_stmt->setAttribute($i_attr, $s_value)->getAttribute($i_attr));
    }

    /**
     * @depends testLazyLoadingForCloseCursor
     * @depends testLazyLoadingForFetch
     * @expectedException Tox\Data\Pdo\ClosedStatementException
     */
    public function testFetchForbiddenAfterCloseCursor()
    {
        $this->pdo->expects($this->never())->method('realize');
        $o_stmt = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt->closeCursor()->fetch();
    }

    /**
     * @depends testLazyLoadingForBindParam
     * @expectedException Tox\Data\Pdo\ExecutedStatementException
     */
    public function testBindParamForbiddenAfterExecute()
    {
        $s_key = microtime();
        $s_val = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->execute()->bindParam($s_key, $s_val);
    }

    /**
     * @depends testLazyLoadingForBindValue
     * @expectedException Tox\Data\Pdo\ExecutedStatementException
     */
    public function testBindValueForbiddenAfterExecute()
    {
        $s_key = microtime();
        $s_val = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->execute()->bindValue($s_key, $s_val);
    }

    public function testOmissibleExecuting()
    {
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->at(0))->method('execute');
        $o_stmt1->expects($this->at(1))->method('fetch');
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->fetch();
    }

    /**
     * @depends testOmissibleExecuting
     * @depends testLazyLoadingForBindValue
     * @depends testLazyLoadingForSetAttribute
     * @depends testLazyLoadingForSetFetchMode
     */
    public function testBindingsWouldBeAffectedOnFetching()
    {
        $s_key1 = microtime();
        $i_key2 = rand();
        $f_val1 = 1 + microtime(true);
        $f_val2 = 2 + microtime(true);
        $f_val3 = 3 + microtime(true);
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('bindValue')
            ->with($this->equalTo($s_key1), $this->equalTo($f_val1));
        $o_stmt1->expects($this->once())->method('setAttribute')
            ->with($this->equalTo($i_key2), $this->equalTo($f_val2));
        $o_stmt1->expects($this->once())->method('setFetchMode')
            ->with($this->equalTo(intval($f_val3)));
        $o_stmt1->expects($this->at(3))->method('execute');
        $o_stmt1->expects($this->at(4))->method('fetch');
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->bindValue($s_key1, $f_val1)
            ->setAttribute($i_key2, $f_val2)->setFetchMode($f_val3)
            ->fetch();
    }

    /**
     * @depends testBindingsWouldBeAffectedOnFetching
     */
    public function testBindParamOppositeBindValue()
    {
        $s_key1 = microtime();
        $s_key2 = microtime();
        $f_val1 = 1 + microtime(true);
        $f_val2 = 2 + microtime(true);
        $f_val3 = 3 + microtime(true);
        $f_val4 = 4 + microtime(true);
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->at(0))->method('bindParam')
            ->with($this->equalTo($s_key1), $this->equalTo($f_val2));
        $o_stmt1->expects($this->at(1))->method('bindParam')
            ->with($this->equalTo($s_key2), $this->equalTo($f_val3));
        $o_stmt1->expects($this->at(2))->method('bindValue')
            ->with($this->equalTo($s_key2), $this->equalTo($f_val4));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->bindValue($s_key1, $f_val1)->bindParam($s_key1, $f_val2)
            ->bindParam($s_key2, $f_val3)->bindValue($s_key2, $f_val4);
    }

    /**
     * @depends testLazyLoadingForExecute
     */
    public function testMultipleExecuteIgnored()
    {
        $m_ret = microtime();
        $a_pars = array(microtime());
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('execute')
            ->with($this->equalTo($a_pars));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $this->assertSame($o_stmt2, $o_stmt2->execute($a_pars)->execute(array()));
    }

    /**
     * @depends testLazyLoadingForBindColumn
     * @expectedException Tox\Data\Pdo\ColumnBindingFailureException
     */
    public function testFailureOfBindColumn()
    {
        $i_key = rand();
        $s_val = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('bindColumn')
            ->will($this->throwException(new Exception));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->bindColumn($i_key, $s_val);
    }

    /**
     * @depends testLazyLoadingForBindParam
     * @expectedException Tox\Data\Pdo\ParamBindingFailureException
     */
    public function testFailureOfBindParam()
    {
        $s_key = microtime();
        $s_val = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('bindParam')
            ->will($this->throwException(new Exception));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->bindParam($s_key, $s_val);
    }

    /**
     * @depends testLazyLoadingForBindValue
     * @expectedException Tox\Data\Pdo\ValueBindingFailureException
     */
    public function testFailureOfBindValue()
    {
        $s_key = microtime();
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('bindValue')
            ->will($this->throwException(new Exception));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->bindParam($s_key, $s_key)->bindValue($s_key, microtime());
    }

    /**
     * @depends testLazyLoadingForCloseCursor
     * @expectedException Tox\Data\Pdo\CursorClosingFailureException
     */
    public function testFailureOfCloseCursor()
    {
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('closeCursor')
            ->will($this->throwException(new Exception));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->fetch();
        $o_stmt2->closeCursor();
    }

    /**
     * @depends testLazyLoadingForExecute
     * @expectedException Tox\Data\Pdo\ExecutingFailureException
     */
    public function testFailureOfExecute()
    {
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('execute')
            ->will($this->throwException(new Exception));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->execute();
    }

    /**
     * @depends testLazyLoadingForNextRowset
     * @expectedException Tox\Data\Pdo\RowsetIteratingFailureException
     */
    public function testFailureOfNextRowset()
    {
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('nextRowset')
            ->will($this->throwException(new Exception));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->nextRowset();
    }

    /**
     * @depends testLazyLoadingForSetAttribute
     * @expectedException Tox\Data\Pdo\AttributeSettingFailureException
     */
    public function testFailureOfSetAttribute()
    {
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('setAttribute')
            ->will($this->throwException(new Exception));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->execute()->setAttribute(rand(), microtime());
    }

    /**
     * @depends testLazyLoadingForSetFetchMode
     * @expectedException Tox\Data\Pdo\FetchModeSettingFailureException
     */
    public function testFailureOfSetFetchMode()
    {
        $o_stmt1 = $this->getMock('Tox\\Data\\IPdoStatement');
        $o_stmt1->expects($this->once())->method('setFetchMode')
            ->will($this->throwException(new Exception));
        $this->pdo->expects($this->once())->method('realize')
            ->will($this->returnValue($o_stmt1));
        $o_stmt2 = $this->getMockForAbstractClass(
            'Tox\\Data\\Pdo\\Statement',
            array($this->pdo, microtime())
        );
        $o_stmt2->execute()->setFetchMode(rand(), microtime());
    }

    public function testCount()
    {
        $i_cnt = rand(1, 10);
        $a_rows = array_fill(0, $i_cnt, array());
        $o_stmt = $this->getMock(
            'Tox\\Data\\Pdo\\Statement',
            array('fetchAll'),
            array($this->pdo, microtime())
        );
        $o_stmt->expects($this->once())->method('fetchAll')
            ->will($this->returnValue($a_rows));
        $this->assertEquals($i_cnt, count($o_stmt));
    }

    /**
     * @depends testCount
     */
    public function testIteration()
    {
        $i_cnt = rand(1, 10);
        $a_rows = array();
        for ($ii = 0; $ii < $i_cnt; $ii++) {
            $a_rows[] = array(
                'id' => microtime(),
                'time' => microtime(true)
            );
        }
        $o_stmt = $this->getMock(
            'Tox\\Data\\Pdo\\Statement',
            array('fetchAll'),
            array($this->pdo, microtime())
        );
        $o_stmt->expects($this->once())->method('fetchAll')
            ->will($this->returnValue($a_rows));
        foreach ($o_stmt as $ii => $jj) {
            $this->assertEquals($a_rows[$ii], $jj);
        }
    }

    public function testManufacturingPreparedStatement()
    {
        $s_val = microtime();
        $o_stmt = $this->getMock(
            'Tox\\Data\\Pdo\\Statement',
            array('newPrepare', 'newQuery'),
            array($this->pdo, microtime())
        );
        $o_stmt->staticExpects($this->once())->method('newPrepare')
            ->will($this->returnValue($s_val));
        $this->assertEquals($s_val, $o_stmt::manufacture($this->pdo, Statement::TYPE_PREPARE, microtime()));
    }

    public function testManufacturingQueryStatement()
    {
        $s_val = microtime();
        $o_stmt = $this->getMock(
            'Tox\\Data\\Pdo\\Statement',
            array('newPrepare', 'newQuery'),
            array($this->pdo, microtime())
        );
        $o_stmt->staticExpects($this->once())->method('newQuery')
            ->will($this->returnValue($s_val));
        $this->assertEquals($s_val, $o_stmt::manufacture($this->pdo, Statement::TYPE_QUERY, microtime()));
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
