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
require_once __DIR__ . '/../../../../src/application/model/illegalsetelementexception.php';
require_once __DIR__ . '/../../../../src/application/model/setpropertyunreplacableexception.php';

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
            '',
            false
        );
    }

    public function testMagicMethods()
    {
        $o_mod = $this->getMockForAbstractClass(
            'Tox\\Application\\Model\\Model',
            array(),
            '',
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
        $o_mod1 = $this->getMockForAbstractClass(
            'Tox\\Application\\Model\\Model',
            array(),
            '',
            false,
            true,
            true,
            array('newModel')
        );
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
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
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
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
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
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
        $this->dao->expects($this->never())->method('create');
        $o_mod2 = $o_mod1::prepare(array('id' => microtime(), 'foo' => microtime()), $this->dao);
        $this->assertInstanceOf(get_class($o_mod1), $o_mod2);
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
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
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
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
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
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
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
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
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

    /**
     * @depends testLoadBySpecificDao
     */
    public function testLoadBySpecificDaoThroughSetUp()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $this->model->setUp($s_id, $this->dao);
        $this->assertInstanceOf(get_class($this->model), $o_mod2);
        $this->assertEquals($s_id, $o_mod2->getId());
        $this->assertTrue($o_mod2->isAlive());
        $this->assertFalse($o_mod2->isChanged());
    }

    /**
     * @depends testLoadBySpecificDao
     */
    public function testEnableAndDisableAsyncMode()
    {
        $s_id = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id)));
        $o_mod2 = $this->model->load($s_id, $this->dao);
        $this->assertTrue($o_mod2->isAsync());
        $this->assertSame($o_mod2, $o_mod2->disableAsync());
        $this->assertFalse($o_mod2->isAsync());
        $this->assertSame($o_mod2, $o_mod2->enableAsync());
        $this->assertTrue($o_mod2->isAsync());
    }

    /**
     * @depends testTerminateWouldNotDeleteBeforeCommit
     * @depends testEnableAndDisableAsyncMode
     */
    public function testTerminateImmediatelyInSyncMode()
    {
        $s_id1 = microtime();
        $s_id2 = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id1))
            ->will($this->returnValue(array('id' => $s_id2)));
        $this->dao->expects($this->once())->method('delete')
            ->with($this->equalTo($s_id2));
        $o_mod2 = $this->model->load($s_id1, $this->dao)->disableAsync();
        $this->assertSame($o_mod2, $o_mod2->terminate());
    }

    /**
     * @depends testChangementsAffecteOnCommit
     * @depends testEnableAndDisableAsyncMode
     */
    public function testModifyImmediatelyInSyncMode()
    {
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
        $s_id = microtime();
        $s_foo1 = microtime();
        $s_foo2 = microtime();
        $this->dao->expects($this->once())->method('read')
            ->with($this->equalTo($s_id))
            ->will($this->returnValue(array('id' => $s_id, 'foo' => $s_foo1)));
        $this->dao->expects($this->once())->method('update')
            ->with($this->equalTo($s_id), $this->equalTo(array('foo' => $s_foo2)));
        $o_mod2 = $o_mod1::load($s_id, $this->dao)->disableAsync();
        $o_mod2->foo = $s_foo2;
        $this->assertEquals($s_foo2, $o_mod2->foo);
    }

    public function testImportFromModelSet()
    {
        $a_rows = array(
            array('id' => microtime(), 'foo' => microtime())
        );
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
        $o_set = $this->getMock('Tox\\Application\\IModelSet');
        $o_set->expects($this->once())->method('current')
            ->will($this->returnValue($a_rows[0]));
        $o_mod2 = $o_mod1::import($o_set, $this->dao);
        $this->assertInstanceOf(get_class($o_mod1), $o_mod2);
        $this->assertEquals($a_rows[0]['id'], $o_mod2->getId());
        $this->assertEquals($a_rows[0]['foo'], $o_mod2->foo);
        $this->assertTrue($o_mod2->isAlive());
        $this->assertFalse($o_mod2->isChanged());
    }

    /**
     * @depends testImportFromModelSet
     * @expectedException Tox\Application\Model\IllegalSetElementException
     */
    public function testImportFromMadModelSet()
    {
        $o_set = $this->getMock('Tox\\Application\\IModelSet');
        $o_set->expects($this->once())->method('current')
            ->will($this->returnValue(array()));
        $this->model->import($o_set, $this->dao);
    }

    /**
     * @depends testLoadOnlyOnce
     * @depends testImportFromModelSet
     */
    public function testImportAsSameAsLoad()
    {
        $a_rows = array(
            array('id' => microtime(), 'foo' => microtime())
        );
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
        $o_set = $this->getMock('Tox\\Application\\IModelSet');
        $o_set->expects($this->once())->method('current')
            ->will($this->returnValue($a_rows[0]));
        $o_mod2 = $o_mod1::import($o_set, $this->dao);
        $this->dao->expects($this->never())->method('read');
        $o_mod3 = $o_mod1::load($a_rows[0]['id'], $this->dao);
        $this->assertSame($o_mod2, $o_mod3);
    }

    /**
     * @depends testLoadBySpecificDao
     * @depends testImportFromModelSet
     */
    public function testImportUpdateAttributes()
    {
        $a_rows = array(
            array('id' => microtime(), 'foo' => microtime()),
            array('id' => microtime(), 'foo' => microtime())
        );
        $a_rows[1]['id'] = $a_rows[0]['id'];
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
        $this->dao->expects($this->once())->method('read')
            ->will($this->returnValue($a_rows[0]));
        $o_mod2 = $o_mod1::load($a_rows[0]['id'], $this->dao);
        $o_set = $this->getMock('Tox\\Application\\IModelSet');
        $o_set->expects($this->once())->method('current')
            ->will($this->returnValue($a_rows[1]));
        $o_mod3 = $o_mod1::import($o_set, $this->dao);
        $this->assertSame($o_mod2, $o_mod3);
        $this->assertEquals($a_rows[1]['foo'], $o_mod3->foo);
    }

    /**
     * @depends testPrepareWouldNotCreateBeforeCommit
     */
    public function testCommittablePropertiesReadableImmediately()
    {
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
        $o_mod2 = $this->getMock('Tox\\Application\\IModel');
        $o_mod3 = $o_mod1::prepare(array('id' => microtime(), 'foo' => $o_mod2), $this->dao);
        $this->assertSame($o_mod2, $o_mod3->foo);
        $o_mod4 = $this->getMock('Tox\\Application\\IModel');
        $o_mod3->foo = $o_mod4;
        $this->assertSame($o_mod4, $o_mod3->foo);
        $o_set1 = $this->getMock('Tox\\Application\\IModelSet');
        $o_mod5 = $o_mod1::prepare(array('id' => microtime(), 'foo' => $o_set1), $this->dao);
        $this->assertSame($o_set1, $o_mod5->foo);
    }

    /**
     * @depends testCommittablePropertiesReadableImmediately
     * @expectedException Tox\Application\Model\SetPropertyUnreplacableException
     */
    public function testSetPropertiesUnreplacable()
    {
        $o_mod1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\ModelDummy', array(), '', false);
        $o_set1 = $this->getMock('Tox\\Application\\IModelSet');
        $o_mod2 = $o_mod1::prepare(array('id' => microtime(), 'foo' => $o_set1), $this->dao);
        $o_mod2->foo = $this->getMock('Tox\\Application\\IModelSet');
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
