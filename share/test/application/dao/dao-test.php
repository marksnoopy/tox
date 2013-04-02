<?php
/**
 * Defines the test case for Tox\Application\Dao\Dao.
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

namespace Tox\Application\Dao;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/core/isingleton.php';
require_once __DIR__ . '/../../../../src/application/idao.php';
require_once __DIR__ . '/../../../../src/application/dao/dao.php';

require_once __DIR__ . '/../../../../src/core/exception.php';

require_once __DIR__ . '/../../../../src/data/isource.php';

/**
 * Tests Tox\Application\Dao\Dao.
 *
 * @internal
 *
 * @package tox.application.dao
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class DaoTest extends PHPUnit_Framework_TestCase
{
    public function testBindingAndRetrievingDataDomain()
    {
        $o_dd = $this->getMock('Tox\\Data\\ISource');
        $this->assertNull(Dao::bindDomain($o_dd));
        $o_dao = $this->getMockForAbstractClass('Tox\\Application\\Dao\\DaoMock', array(), '', false);
        $this->assertSame($o_dd, $o_dao->getDomainTest());
    }

    /**
     * @depends testBindingAndRetrievingDataDomain
     */
    public function testLeafDataDomainWouldBeUsed()
    {
        $o_dd1 = $this->getMock('Tox\\Data\\ISource');
        Dao::bindDomain($o_dd1);
        $o_dao1 = $this->getMockForAbstractClass(
            'Tox\\Application\\Dao\\DaoMock',
            array(),
            'c' . md5(microtime()),
            false
        );
        $o_dd2 = $this->getMock('Tox\\Data\\ISource');
        call_user_func(array(get_class($o_dao1), 'bindDomain'), $o_dd2);
        $this->assertSame($o_dd2, $o_dao1->getDomainTest());
        $o_dao2 = $this->getMockForAbstractClass(
            'Tox\\Application\\Dao\\DaoMock',
            array(),
            'c' . md5(microtime()),
            false
        );
        $this->assertSame($o_dd1, $o_dao2->getDomainTest());
    }

    public function testSingletonPattern()
    {
        $s_sub1 = 'c' . md5(microtime());
        $this->getMockForAbstractClass('Tox\\Application\\Dao\\Dao', array(), $s_sub1, false);
        $s_sub2 = 'c' . md5(microtime());
        $this->getMockForAbstractClass('Tox\\Application\\Dao\\Dao', array(), $s_sub2, false);
        $o_dao1 = call_user_func(array($s_sub1, 'getInstance'));
        $this->assertInstanceOf($s_sub1, $o_dao1);
        $o_dao2 = call_user_func(array($s_sub2, 'getInstance'));
        $this->assertInstanceOf($s_sub2, $o_dao2);
        $o_dao3 = call_user_func(array($s_sub1, 'getInstance'));
        $this->assertSame($o_dao1, $o_dao3);
    }
}

/**
 * Represents as a derived data access object for mocking test.
 *
 * @internal
 *
 * @package tox.application.dao
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class DaoMock extends Dao
{
    /**
     * Retrieves the binded data domain.
     *
     * @return \Tox\Data\ISource
     */
    public function getDomainTest()
    {
        return $this->getDomain();
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
