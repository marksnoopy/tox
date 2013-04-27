<?php
/**
 * Defines the test case for Tox\Data\Pdo\Query.
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
require_once __DIR__ . '/../../../../src/data/pdo/query.php';

require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/data/pdo/executedstatementexception.php';

require_once __DIR__ . '/../../../../src/data/isource.php';
require_once __DIR__ . '/../../../../src/data/ipdo.php';

/**
 * Tests Tox\Data\Pdo\Query.
 *
 * @internal
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class QueryTest extends PHPUnit_Framework_TestCase
{
    /**
     * Stores the mocking data object for testing.
     *
     * @var \Tox\Data\IPdo
     */
    protected $pdo;

    protected function setUp()
    {
        $this->pdo = $this->getMock('Tox\\Data\\IPdo');
    }

    public function testPersistantType()
    {
        $o_stmt = new Query($this->pdo, microtime());
        $this->assertEquals(Query::TYPE_QUERY, $o_stmt->getType());
    }

    /**
     * @expectedException Tox\Data\Pdo\ExecutedStatementException
     */
    public function testBindParamForbidden()
    {
        $s_val = microtime();
        $o_stmt = new Query($this->pdo, microtime());
        $o_stmt->bindParam(microtime(), $s_val);
    }

    /**
     * @expectedException Tox\Data\Pdo\ExecutedStatementException
     */
    public function testBindValueForbidden()
    {
        $o_stmt = new Query($this->pdo, microtime());
        $o_stmt->bindValue(microtime(), microtime());
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
