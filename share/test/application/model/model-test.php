<?php
/**
 * Defines the test case for Tox\Application\Model\Model.
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

namespace Tox\Application\Model;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/application/icommittable.php';
require_once __DIR__ . '/../../../../src/application/imodel.php';
require_once __DIR__ . '/../../../../src/application/model/model.php';

require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/application/model/identifierreadonlyexception.php';
require_once __DIR__ . '/../../../../src/application/model/nonexistantentityexception.php';
require_once __DIR__ . '/../../../../src/application/model/preparationcannotresetexception.php';
require_once __DIR__ . '/../../../../src/application/model/duplicateidentifierexception.php';

require_once __DIR__ . '/../../../../src/application/imodelset.php';
require_once __DIR__ . '/../../../../src/core/isingleton.php';
require_once __DIR__ . '/../../../../src/application/idao.php';
require_once __DIR__ . '/../../../../src/data/isource.php';

/**
 * Tests Tox\Application\Model\Model.
 *
 * @internal
 *
 * @package tox.application.model
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ModelTest extends PHPUnit_Framework_TestCase
{
    /**
     * Stores the mock of data access object for test.
     *
     * @var Tox\Application\IDao
     */
    protected $dao;

    /**
     * Stores the dummy of model entity for test.
     *
     * @var Model
     */
    protected $model;

    protected function setUp()
    {
        $this->dao = $this->getMock('Tox\\Application\\IDao');
        $this->model = $this->getMockForAbstractClass(
            'Tox\\Application\\Model\\Model',
            array(),
            'c_' . sha1(microtime()),
            false
        );
    }

    public function testMagicMethods()
    {
        $s_class = 'c_' . sha1(microtime());
        $o_mod = $this->getMockForAbstractClass(
            'Tox\\Application\\Model\\Model',
            array(),
            $s_class,
            false,
            true,
            true,
            array('getId', 'setId')
        );
        $s_id = microtime();
        $o_mod->expects($this->once())->method('getId')
            ->will($this->returnValue($s_id));
        $o_mod->expects($this->once())->method('setId')
            ->with($this->equalTo($s_id));
        $this->assertEquals($s_id, $o_mod->id);
        $o_mod->id = $s_id;
    }

    public function testLoadBySpecificDao()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $this->assertInstanceOf(get_class($this->model), $o_mod2);
        $this->assertEquals($s_id, $o_mod2->getId());
        $this->assertTrue($o_mod2->isAlive());
        $this->assertFalse($o_mod2->isChanged());
    }

    /**
     * @depends testLoadBySpecificDao
     */
    public function testLoadByDefaultDao()
    {
        $s_class = 'c_' . sha1(microtime());
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\Model',
            array(),
            $s_class,
            false,
            true,
            true,
            array('newModel'));
        $o_mod1->expects($this->once())->method('getDefaultDao')
            ->will($this->returnValue($this->dao));
        $o_mod1->staticExpects($this->once())->method('newModel')
            ->will($this->returnValue($o_mod1));
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $o_mod1::load($s_id);
        $this->assertSame($o_mod2, $o_mod1);
        $this->assertEquals($s_id, $o_mod2->getId());
    }

    /**
     * @depends testLoadBySpecificDao
     */
    public function testAttributesAssignedOnLoad()
    {
        $s_class = 'c_' . sha1(microtime());
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), $s_class, false);
        $s_id = microtime();
        $s_foo = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id, 'foo' => $s_foo)));
        $o_mod2 = $o_mod1::load($s_id, $this->dao);
        $this->assertEquals($s_foo, $o_mod2->foo);
    }

    /**
     * @depends testAttributesAssignedOnLoad
     */
    public function testAttributesUnchangedBeforeCommit()
    {
        $s_class = 'c_' . sha1(microtime());
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), $s_class, false);
        $s_id = microtime();
        $s_foo1 = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id, 'foo' => $s_foo1)));
        $o_mod2 = $o_mod1::load($s_id, $this->dao);
        $s_foo2 = microtime();
        $o_mod2->foo = $s_foo2;
        $this->assertEquals($s_foo1, $o_mod2->foo);
        $this->assertTrue($o_mod2->isChanged());
    }

    public function testPrepareWouldNotCreateBeforeCommit()
    {
        $s_class = 'c_' . sha1(microtime());
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), $s_class, false);
        $this->dao->expects($this->never())->method('create');
        $o_mod2 = $o_mod1::prepare(array('id' => microtime(), 'foo' => microtime()), $this->dao);
        $this->assertInstanceOf($s_class, $o_mod2);
        $this->assertNull($o_mod2->getId());
        $this->assertNull($o_mod2->foo);
        $this->assertFalse($o_mod2->isAlive());
        $this->assertTrue($o_mod2->isChanged());
    }

    /**
     * @depends testLoadBySpecificDao
     */
    public function testTerminateWouldNotDeleteBeforeCommit()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $this->dao->expects($this->never())->method('delete');
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $this->assertSame($o_mod2, $o_mod2->terminate());
        $this->assertEquals($s_id, $o_mod2->getId());
        $this->assertTrue($o_mod2->isAlive());
        $this->assertTrue($o_mod2->isChanged());
    }

    /**
     * @depends testAttributesUnchangedBeforeCommit
     */
    public function testChangementsAffecteOnCommit()
    {
        $s_class = 'c_' . sha1(microtime());
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), $s_class, false);
        $s_id = microtime();
        $s_foo1 = microtime();
        $s_foo2 = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id, 'foo' => $s_foo1)));
        $this->dao->expects($this->once())->method('update')
            ->with($this->equalTo($s_id), $this->equalTo(array('foo' => $s_foo2)));
        $o_mod2 = $o_mod1::load($s_id, $this->dao);
        $o_mod2->foo = $s_foo2;
        $this->assertSame($o_mod2, $o_mod2->commit());
        $this->assertEquals($s_foo2, $o_mod2->foo);
    }

    /**
     * @depends testPrepareWouldNotCreateBeforeCommit
     */
    public function testCreationOnCommit()
    {
        $s_id1 = microtime();
        $s_id2 = microtime();
        $this->dao->expects($this->once())->method('create')
            ->with($this->equalTo(array('id' => $s_id1)))
            ->will($this->returnValue($s_id2));
        $o_mod2 = $this->model->prepare(array('id' => $s_id1), $this->dao);
        $o_mod2->commit();
        $this->assertEquals($s_id2, $o_mod2->getId());
    }

    /**
     * @depends testTerminateWouldNotDeleteBeforeCommit
     */
    public function testDeletionOnCommit()
    {
        $s_class = 'c_' . sha1(microtime());
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), $s_class, false);
        $s_id = microtime();
        $s_foo = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id, 'foo' => $s_foo)));
        $this->dao->expects($this->once())->method('delete')
            ->with($this->equalTo($s_id));
        $o_mod2 = $o_mod1::load($s_id, $this->dao);
        $o_mod2->terminate()->commit();
        $this->assertNull($o_mod2->getId());
        $this->assertEquals($s_foo, $o_mod2->foo);
        $this->assertFalse($o_mod2->isAlive());
        $this->assertFalse($o_mod2->isChanged());
    }

    /**
     * @depends testAttributesUnchangedBeforeCommit
     */
    public function testResetChangements()
    {
        $s_class = 'c_' . sha1(microtime());
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), $s_class, false);
        $s_id = microtime();
        $s_foo1 = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id, 'foo' => $s_foo1)));
        $o_mod2 = $o_mod1::load($s_id, $this->dao);
        $s_foo2 = microtime();
        $o_mod2->foo = $s_foo2;
        $this->assertSame($o_mod2, $o_mod2->reset());
        $this->assertFalse($o_mod2->isChanged());
        $o_mod2->commit();
        $this->assertEquals($s_foo1, $o_mod2->foo);
    }

    /**
     * @depends testPrepareWouldNotCreateBeforeCommit
     * @expectedException Tox\Application\Model\PreparationCanNotResetException
     */
    public function testPreparationCanNotReset()
    {
        $this->dao->expects($this->never())->method('create');
        $o_mod2 = $this->model->prepare(array('id' => microtime()), $this->dao);
        $o_mod2->reset();
    }

    /**
     * @depends testDeletionOnCommit
     */
    public function testResetTermination()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $o_mod2->terminate()->reset();
        $this->assertFalse($o_mod2->isChanged());
        $o_mod2->commit();
        $this->assertEquals($s_id, $o_mod2->getId());
    }

    /**
     * @depends testLoadBySpecificDao
     */
    public function testStringCasting()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $this->assertEquals($s_id, (string) $o_mod2);
    }

    /**
     * @depends testStringCasting
     * @depends testPrepareWouldNotCreateBeforeCommit
     */
    public function testStringCastingFailureForPrepared()
    {
        $o_mod2 = $this->model->prepare(array('id' => microtime()), $this->dao);
        $this->assertEquals('', (string) $o_mod2);
    }

    /**
     * @depends testStringCasting
     * @depends testDeletionOnCommit
     */
    public function testStringCastingFailureForTerminated()
    {
        $s_id = microtime();
        $s_foo = microtime();
        $this->dao->expects($this->once())->method('read')
            ->will($this->returnValue(array('id' => microtime())));
        $this->dao->expects($this->once())->method('delete');
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $o_mod2->terminate()->commit();
        $this->assertEquals('', (string) $o_mod2);
    }

    /**
     * @depends testLoadByDefaultDao
     */
    public function testLoadOnlyOnce()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $o_dao = $this->getMock('Tox\\Application\\IDao');
        $o_dao->expects($this->never())->method('read');
        $o_mod3 = $this->model->load($s_id, $o_dao);
        $this->assertSame($o_mod2, $o_mod3);
    }

    /**
     * @depends testCreationOnCommit
     */
    public function testPrepareThroughClone()
    {
        $s_class = 'c_' . sha1(microtime());
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), $s_class, false);
        $s_id1 = microtime();
        $s_foo = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id1))
            ->will($this->returnValue(array('id' => $s_id1, 'foo' => $s_foo)));
        $o_mod2 = $o_mod1::load($s_id1, $this->dao);
        $o_mod3 = clone $o_mod2;
        $this->assertFalse($o_mod3->isAlive());
        $this->assertTrue($o_mod3->isChanged());
        $s_id2 = microtime();
        $this->dao->expects($this->once())->method('create')
            ->with($this->equalTo(array('id' => null, 'foo' => $s_foo)))
            ->will($this->returnValue($s_id2));
        $o_mod3->commit();
        $this->assertEquals($s_id2, $o_mod3->getId());
        $this->assertEquals($s_foo, $o_mod3->foo);
    }

    /**
     * @expectedException Tox\Application\Model\IdentifierReadOnlyException
     */
    public function testIdFrozenForExistant()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $o_mod2->setId(microtime());
    }

    /**
     * @depends testCreationOnCommit
     */
    public function testIdChangableForPrepared()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('create')
            ->with($this->equalTo(array('id' => $s_id)))
            ->will($this->returnValue($s_id));
        $o_mod2 = $this->model->prepare(array('id' => microtime()), $this->dao);
        $o_mod2->setId($s_id);
        $o_mod2->commit();
        $this->assertEquals($s_id, $o_mod2->getId());
    }

    /**
     * @depends testDeletionOnCommit
     * @expectedException Tox\Application\Model\IdentifierReadOnlyException
     */
    public function testIdFrozenForDead()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $o_mod2->terminate()->commit()->setId(microtime());
    }

    /**
     * @depends testDeletionOnCommit
     * @depends testLoadOnlyOnce
     */
    public function testTerminatedStillLoadOnlyOnce()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $o_mod2->terminate()->commit();
        $o_mod3 = $this->model->load($s_id, $this->dao);
        $this->assertSame($o_mod2, $o_mod3);
    }

    /**
     * @depends testCreationOnCommit
     * @depends testLoadOnlyOnce
     */
    public function testPreparedStillLoadOnlyOnce()
    {
        $s_id1 = microtime();
        $s_id2 = microtime();
        $this->dao->expects($this->once())->method('create')
            ->with($this->equalTo(array('id' => $s_id1)))
            ->will($this->returnValue($s_id2));
        $this->dao->expects($this->never())->method('read');
        $o_mod2 = $this->model->prepare(array('id' => $s_id1), $this->dao);
        $o_mod2->commit();
        $o_mod3 = $this->model->load($s_id2, $this->dao);
        $this->assertSame($o_mod2, $o_mod3);
    }

    /**
     * @depends testPreparedStillLoadOnlyOnce
     * @expectedException Tox\Application\Model\DuplicateIdentifierException
     */
    public function testDuplicateIdentifierOnCreation()
    {
        $s_id1 = microtime();
        $s_id2 = microtime();
        $this->dao->expects($this->once())->method('create')
            ->with($this->equalTo(array('id' => $s_id1)))
            ->will($this->returnValue($s_id2));
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id2))
            ->will($this->returnValue(array('id' => $s_id2)));
        $o_mod2 = $this->model->load($s_id2, $this->dao);
        $o_mod3 = $this->model->prepare(array('id' => $s_id1), $this->dao);
        $o_mod3->commit();
    }
}

/**
 * Represents as a derived model entity for mocking test.
 *
 * @internal
 *
 * @package tox.application.model
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class ModelDummy extends Model
{
    /**
     * Stores the foo attribute.
     *
     * @var string
     */
    protected $foo;

    /**
     * Be invoked on retrieving foo.
     *
     * @return string
     */
    protected function toxGetFoo()
    {
        return $this->foo;
    }

    /**
     * Be invoked on setting foo.
     *
     * @param  mixed $value New value.
     * @return void
     */
    protected function toxSetFoo($value)
    {
        $this->foo = $value;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
