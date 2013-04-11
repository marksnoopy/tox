<?php
/**
 * Defines the test case for Tox\Data\Pdo\Pdo.
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
require_once __DIR__ . '/../../../../src/data/pdo/pdo.php';

require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/data/pdo/nestedtransactionunsupportedexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/noactivetransactionexception.php';
require_once __DIR__ . '/../../../../src/data/pdo/dismatchedstatementexception.php';

require_once __DIR__ . '/../../../../src/data/ipdostatement.php';

use Tox\Data;

/**
 * Tests Tox\Data\Pdo\Pdo.
 *
 * @internal
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class PdoTest extends PHPUnit_Framework_TestCase
{
    /**
     * Stores the testing data source name.
     *
     * @var string
     */
    protected $dsn;

    /**
     * Stores the testing user name.
     *
     * @var string
     */
    protected $username;

    /**
     * Stores the testing password.
     *
     * @var string
     */
    protected $password;

    /**
     * Stores the testing connection attributes.
     *
     * @var mixed[]
     */
    protected $options;

    /**
     * Stores the mocking object of the data object.
     *
     * @var PDO
     */
    protected $pdoMock;

    /**
     * Stores another mocking object of PHP data object.
     * @var Tox\Data\IPdo
     */
    protected $pdo;

    /**
     * Stores all available options and values.
     *
     * @var array
     */
    protected static $availableOptions = array(
        Pdo::ATTR_CASE => array(
            Pdo::CASE_NATURAL,
            Pdo::CASE_LOWER,
            Pdo::CASE_UPPER
        ),
        Pdo::ATTR_ERRMODE => array(
            Pdo::ERRMODE_SILENT,
            Pdo::ERRMODE_WARNING,
            Pdo::ERRMODE_EXCEPTION
        ),
        Pdo::ATTR_ORACLE_NULLS => array(
            Pdo::NULL_NATURAL,
            Pdo::NULL_EMPTY_STRING,
            Pdo::NULL_TO_STRING
        ),
        Pdo::ATTR_STRINGIFY_FETCHES => 'bool',
        Pdo::ATTR_TIMEOUT => 'int',
        Pdo::ATTR_AUTOCOMMIT => 'bool',
        Pdo::ATTR_EMULATE_PREPARES => 'bool',
        Pdo::MYSQL_ATTR_USE_BUFFERED_QUERY => 'bool',
        Pdo::ATTR_DEFAULT_FETCH_MODE => array(
            Pdo::FETCH_BOTH,
            Pdo::FETCH_ASSOC,
            Pdo::FETCH_BOUND,
            Pdo::FETCH_CLASS,
            Pdo::FETCH_INTO,
            Pdo::FETCH_LAZY,
            Pdo::FETCH_NUM,
            Pdo::FETCH_OBJ
        )
    );

    protected function setUp()
    {
        $s_seed = microtime();
        $this->dsn = sha1('dsn' . $s_seed);
        $this->username = sha1('username' . $s_seed);
        $this->password = sha1('password' . $s_seed);
        $this->options = array();
        $i_len = rand(2, count(static::$availableOptions) - 1);
        $a_opts = array_rand(static::$availableOptions, $i_len);
        for ($ii = 0; $ii < $i_len; $ii++) {
            $ll = static::$availableOptions[$a_opts[$ii]];
            if (is_array($ll)) {
                $this->options[$a_opts[$ii]] = $ll[array_rand($ll)];
            } elseif ('bool' == $ll) {
                $this->options[$a_opts[$ii]] = (bool) mt_rand(0, 1);
            } elseif ('int' == $ll) {
                $this->options[$a_opts[$ii]] = mt_rand();
            }
        }
        $this->pdo = $this->getMock('Tox\\Data\\IPdo');
        $this->pdoMock = $this->getMock(
            'Tox\\Data\\Pdo\\PdoStub',
            array('newPHPPdo', 'newStatement'),
            array($this->dsn, $this->username, $this->password, $this->options),
            'c' . sha1(microtime())
        );
    }

    public function testSingletonForEachDataSourceByTheSameUser()
    {
        $s_class = get_class($this->pdoMock);
        $o_pdo1 = $s_class::getInstance($this->dsn, $this->username, $this->password, $this->options);
        $o_pdo2 = $s_class::getInstance($this->dsn, $this->username, $this->password, $this->options);
        $this->assertSame($o_pdo1, $o_pdo2);
        $o_pdo3 = $s_class::getInstance($this->dsn, $this->username);
        $this->assertSame($o_pdo2, $o_pdo3);
        $o_pdo4 = $s_class::getInstance($this->dsn, sha1(microtime()));
        $this->assertNotSame($o_pdo3, $o_pdo4);
    }

    public function testRetrievingDsn()
    {
        $this->assertEquals($this->dsn, $this->pdoMock->getDsn());
    }

    public function testRetrievingUserName()
    {
        $this->assertEquals($this->username, $this->pdoMock->getUsername());
    }

    /**
     * @depends testRetrievingDsn
     * @depends testRetrievingUserName
     */
    public function testingMagicMethods()
    {
        $o_pdoMock = $this->getMock(
            'Tox\\Data\\Pdo\\PdoStub',
            array('newPHPPdo', 'newStatement', 'getDsn', 'getUsername'),
            array($this->dsn, $this->username, $this->password, $this->options),
            'c' . sha1(microtime())
        );
        $s_value = microtime();
        $o_pdoMock->expects($this->once())->method('getDsn')
            ->will($this->returnValue($s_value));
        $o_pdoMock->expects($this->once())->method('getUsername')
            ->will($this->returnValue($s_value));
        $this->assertEquals($s_value, $o_pdoMock->dsn);
        $this->assertEquals($s_value, $o_pdoMock->username);
    }

    public function testLazyLoadingForBeginTransaction()
    {
        $this->pdo->expects($this->never())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->assertTrue($this->pdoMock->beginTransaction());
        $this->assertTrue($this->pdoMock->inTransaction());
        $this->assertFalse($this->pdoMock->isConnected());
    }

    /**
     * @depends testLazyLoadingForBeginTransaction
     * @expectedException Tox\Data\Pdo\NestedTransactionUnsupportedException
     */
    public function testLazyLoadingForBeginTransaction2()
    {
        $this->pdo->expects($this->never())->method('beginTransaction');
        $this->pdoMock->expects($this->never())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->pdoMock->beginTransaction();
        $this->pdoMock->beginTransaction();
        $this->assertTrue($this->pdoMock->inTransaction());
        $this->assertFalse($this->pdoMock->isConnected());
    }

    /**
     * @expectedException Tox\Data\Pdo\NoActiveTransactionException
     */
    public function testLazyLoadingForCommit()
    {
        $this->pdo->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->never())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->pdoMock->commit();
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->assertFalse($this->pdoMock->isConnected());
    }

    /**
     * @depends testLazyLoadingForCommit
     */
    public function testLazyLoadingForCommit2()
    {
        $this->pdo->expects($this->never())->method('commit');
        $this->pdoMock->expects($this->never())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->pdoMock->beginTransaction();
        $this->assertTrue($this->pdoMock->commit());
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->assertFalse($this->pdoMock->isConnected());
    }

    public function testLazyLoadingForExec()
    {
        $s_sql = microtime();
        $this->pdo->expects($this->once())->method('exec')
            ->with($this->equalTo($s_sql));
        $this->pdoMock->expects($this->once())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->pdoMock->exec($s_sql);
        $this->assertTrue($this->pdoMock->isConnected());
    }

    public function testLazyLoadingForGetAttribute()
    {
        $i_opt = array_rand($this->options);
        $this->pdo->expects($this->never())->method('getAttribute');
        $this->pdoMock->expects($this->any())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->pdoMock->getAttribute($i_opt);
        $this->assertFalse($this->pdoMock->isConnected());
    }

    /**
     * @depends testLazyLoadingForGetAttribute
     */
    public function testLazyLoadingForGetAttribute2()
    {
        $a_solid = array(
            Pdo::ATTR_CASE => Pdo::CASE_NATURAL,
            Pdo::ATTR_ERRMODE => Pdo::ERRMODE_EXCEPTION,
            Pdo::ATTR_DEFAULT_FETCH_MODE => Pdo::FETCH_ASSOC
        );
        $i_opt = array_rand(array_diff_key(static::$availableOptions, $this->options, $a_solid));
        $this->pdo->expects($this->once())->method('getAttribute')
            ->with($this->equalTo($i_opt));
        $this->pdoMock->expects($this->once())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->pdoMock->getAttribute($i_opt);
        $this->assertTrue($this->pdoMock->isConnected());
    }

    public function testLazyLoadingForLastInsertId()
    {
        $this->pdo->expects($this->never())->method('lastInsertId');
        $this->pdoMock->expects($this->any())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->pdoMock->lastInsertId();
        $this->assertFalse($this->pdoMock->isConnected());
    }

    public function testLazyLoadingForPrepare()
    {
        $s_sql = microtime();
        $o_stmt = $this->getMock(
            'Tox\\Data\\IPdoStatement',
            array(),
            array($this->pdoMock, Data\IPdoStatement::TYPE_PREPARE, $s_sql)
        );
        $o_stmt->expects($this->once())->method('getId');
        $this->pdoMock->expects($this->never())->method('newPHPPdo');
        $this->pdoMock->expects($this->once())->method('newStatement')
            ->with($this->equalTo($s_sql), $this->equalTo(Data\IPdoStatement::TYPE_PREPARE))
            ->will($this->returnValue($o_stmt));
        $this->pdoMock->prepare($s_sql, array(microtime()));
        $this->assertFalse($this->pdoMock->isConnected());
    }

    public function testLazyLoadingForQuery()
    {
        $s_sql = microtime();
        $o_stmt = $this->getMock(
            'Tox\\Data\\IPdoStatement',
            array(),
            array($this->pdoMock, Data\IPdoStatement::TYPE_QUERY, $s_sql)
        );
        $this->pdoMock->expects($this->never())->method('newPHPPdo');
        $this->pdoMock->expects($this->once())->method('newStatement')
            ->with($this->equalTo($s_sql), $this->equalTo(Data\IPdoStatement::TYPE_QUERY))
            ->will($this->returnValue($o_stmt));
        $this->pdoMock->query($s_sql);
        $this->assertFalse($this->pdoMock->isConnected());
    }

    public function testLazyLoadingForQuote()
    {
        $s_lob = microtime();
        $i_type = rand();
        $this->pdo->expects($this->once())->method('quote')
            ->with($this->equalTo($s_lob), $this->equalTo($i_type));
        $this->pdoMock->expects($this->once())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->pdoMock->quote($s_lob, $i_type);
        $this->assertTrue($this->pdoMock->isConnected());
    }

    /**
     * @expectedException Tox\Data\Pdo\NoActiveTransactionException
     */
    public function testLazyLoadingForRollBack()
    {
        $this->pdo->expects($this->never())->method('rollBack');
        $this->pdoMock->expects($this->never())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->pdoMock->rollBack();
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->assertFalse($this->pdoMock->isConnected());
    }

    /**
     * @depends testLazyLoadingForRollBack
     */
    public function testLazyLoadingForRollBack2()
    {
        $this->pdo->expects($this->never())->method('rollBack');
        $this->pdoMock->expects($this->never())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->pdoMock->beginTransaction();
        $this->assertTrue($this->pdoMock->rollBack());
        $this->assertFalse($this->pdoMock->inTransaction());
        $this->assertFalse($this->pdoMock->isConnected());
    }

    public function testLazyLoadingForSetAttribute()
    {
        $i_opt = array_rand(static::$availableOptions);
        $this->pdo->expects($this->never())->method('setAttribute');
        $this->pdoMock->expects($this->never())->method('newPHPPdo');
        $this->assertTrue($this->pdoMock->setAttribute($i_opt, microtime()));
    }

    public function testLazyLoadingForSetAttribute2()
    {
        do {
            $i_opt = rand();
        } while (array_key_exists($i_opt, static::$availableOptions));
        $this->pdo->expects($this->never())->method('setAttribute');
        $this->pdoMock->expects($this->never())->method('newPHPPdo');
        $this->assertFalse($this->pdoMock->setAttribute($i_opt, microtime()));
    }

    /**
     * @depends testLazyLoadingForBeginTransaction
     * @depends testLazyLoadingForExec
     * @depends testLazyLoadingForCommit
     */
    public function testLazyBeginTransactionForLazyLoading()
    {
        $this->pdo->expects($this->at(0))->method('beginTransaction');
        $this->pdo->expects($this->at(1))->method('exec');
        $this->pdo->expects($this->at(2))->method('commit');
        $this->pdoMock->expects($this->once())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->pdoMock->beginTransaction();
        $this->pdoMock->exec(microtime());
        $this->pdoMock->commit();
    }

    /**
     * @depends testLazyLoadingForSetAttribute
     * @depends testLazyLoadingForExec
     */
    public function testLazySetAttributeForLazyLoading()
    {
        $a_solid = array(
            Pdo::ATTR_CASE => Pdo::CASE_NATURAL,
            Pdo::ATTR_ERRMODE => Pdo::ERRMODE_EXCEPTION,
            Pdo::ATTR_DEFAULT_FETCH_MODE => Pdo::FETCH_ASSOC
        );
        $i_opt = array_rand(array_diff_key(static::$availableOptions, $this->options, $a_solid));
        $s_value = microtime();
        $this->pdo->expects($this->once())->method('setAttribute')
            ->with($this->equalTo($i_opt), $this->equalTo($s_value));
        $this->pdoMock->expects($this->once())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->pdoMock->exec(microtime());
        $this->pdoMock->setAttribute($i_opt, $s_value);
        $this->pdoMock->setAttribute(array_rand($a_solid), microtime());
    }

    public function testRealizingForPrepare()
    {
        $s_sql = microtime();
        $a_opts = array(microtime());
        $o_stmt = $this->getMock(
            'Tox\\Data\\IPdoStatement',
            array(),
            array($this->pdoMock, Data\IPdoStatement::TYPE_PREPARE, $s_sql)
        );
        $o_stmt->expects($this->once())->method('getType')
            ->will($this->returnValue(Data\IPdoStatement::TYPE_PREPARE));
        $o_stmt->expects($this->once())->method('getPdo')
            ->will($this->returnValue($this->pdoMock));
        $this->pdo->expects($this->once())->method('prepare')
            ->with($this->equalTo($o_stmt), $this->equalTo($a_opts));
        $this->pdoMock->expects($this->once())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->pdoMock->expects($this->once())->method('newStatement')
            ->will($this->returnValue($o_stmt));
        $this->pdoMock->realize($this->pdoMock->prepare($s_sql, $a_opts));
    }

    public function testRealizingForQuery()
    {
        $s_sql = microtime();
        $o_stmt = $this->getMock(
            'Tox\\Data\\IPdoStatement',
            array(),
            array($this->pdoMock, Data\IPdoStatement::TYPE_QUERY, $s_sql)
        );
        $o_stmt->expects($this->once())->method('getType')
            ->will($this->returnValue(Data\IPdoStatement::TYPE_QUERY));
        $o_stmt->expects($this->once())->method('getPdo')
            ->will($this->returnValue($this->pdoMock));
        $this->pdo->expects($this->once())->method('query')
            ->with($this->equalTo($o_stmt));
        $this->pdoMock->expects($this->once())->method('newPHPPdo')
            ->will($this->returnValue($this->pdo));
        $this->pdoMock->realize($o_stmt);
    }

    /**
     * @expectedException Tox\Data\Pdo\DismatchedStatementException
     */
    public function testGeneratedStatementExpectedForRealizing()
    {
        $o_pdo = $this->getMock(
            'Tox\\Data\\Pdo\\PdoStub',
            array('newPHPPdo', 'newStatement'),
            array($this->dsn, $this->username, $this->password, $this->options),
            'c' . sha1(microtime())
        );
        $o_stmt = $this->getMock(
            'Tox\\Data\\IPdoStatement',
            array(),
            array($o_pdo, Data\IPdoStatement::TYPE_QUERY, microtime())
        );
        $o_stmt->expects($this->once())->method('getPdo')
            ->will($this->returnValue($o_pdo));
        $this->pdoMock->realize($o_stmt);
    }

    public function testListingAvailableDrivers()
    {
        $s_class = get_class($this->pdoMock);
        $this->assertEquals(\PDO::getAvailableDrivers(), $s_class::getAvailableDrivers());
    }

    public function testStringCasting()
    {
        $s_lob = get_class($this->pdoMock) . ':' . $this->username . '@' . $this->dsn;
        $this->assertEquals($s_lob, (string) $this->pdoMock);
    }
}

/**
 * Represents as an extended PHP data object for mocking test.
 *
 * @internal
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class PdoStub extends Pdo
{
    /**
     * Cleans every instance up.
     *
     * @return void
     */
    public static function reset()
    {
        static::$instances = null;
    }

    public function __construct($dsn, $user, $pass, $options)
    {
        parent::__construct($dsn, $user, $pass, $options);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
