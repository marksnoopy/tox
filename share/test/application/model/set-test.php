<?php
/**
 * Defines the test case for Tox\Application\Model\Set.
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
require_once __DIR__ . '/../../../../src/application/imodelset.php';
require_once __DIR__ . '/../../../../src/application/model/set.php';

require_once __DIR__ . '/../../../../src/core/exception.php';
require_once __DIR__ . '/../../../../src/application/model/illegalfilterexception.php';
require_once __DIR__ . '/../../../../src/application/model/attributefilteredexception.php';
require_once __DIR__ . '/../../../../src/application/model/attributesortedexception.php';
require_once __DIR__ . '/../../../../src/application/model/illegalentityforsetexception.php';
require_once __DIR__ . '/../../../../src/application/model/modelincludedinsetexception.php';
require_once __DIR__ . '/../../../../src/application/model/preparedmodeltodropexception.php';
require_once __DIR__ . '/../../../../src/application/model/setwithoutfiltersexception.php';

require_once __DIR__ . '/../../../../src/application/imodel.php';
require_once __DIR__ . '/../../../../src/core/isingleton.php';
require_once __DIR__ . '/../../../../src/application/idao.php';
require_once __DIR__ . '/../../../../src/data/isource.php';

/**
 * Tests Tox\Application\Model\Set.
 *
 * @internal
 *
 * @package tox.application.model
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class SetTest extends PHPUnit_Framework_TestCase
{
    /**
     * Stores the mock of data access object for test.
     *
     * @var Tox\Application\IDao
     */
    protected $dao;

    protected function setUp()
    {
        $this->dao = $this->getMock('Tox\\Application\\IDao');
    }

    /**
     * @dataProvider provideFiltersAndExcludes
     */
    public function testMagicFilterAndExclude($func, $func, $attr, $value)
    {
        $value = array_slice(func_get_args(), 3);
        $o_set = $this->getMockForAbstractClass(
            'Tox\\Application\\Model\\Set',
            array(),
            '',
            true,
            true,
            true,
            array($func)
        );
        $a_args = array($this->equalTo($attr));
        foreach ($value as $ii => $jj) {
            $a_args[1 + $ii] = $this->equalTo($jj);
        }
        call_user_func_array(array($o_set->expects($this->once())->method($func), 'with'), $a_args);
        $i_pos = ('filter' == substr($func, 0, 6)) ? 6 : 7;
        $s_func = substr($func, 0, $i_pos) . $attr . substr($func, $i_pos);
        call_user_func_array(array($o_set, $s_func), $value);
    }

    /**
     * @depends testMagicFilterAndExclude
     */
    public function testFilterBetweenConvertedToFilterEqualsOnPoint()
    {
        $f_val1 = microtime(true);
        $f_val2 = microtime(true);
        $s_attr = sha1(microtime());
        $o_set = $this->getMockForAbstractClass(
            'Tox\\Application\\Model\\Set',
            array(),
            '',
            true,
            true,
            true,
            array('doFilter')
        );
        $o_set->expects($this->at(0))->method('doFilter')
            ->with(
                $this->equalTo('Tox\\Application\\Model\\Set::filterEquals'),
                $this->equalTo($s_attr),
                $this->equalTo($f_val1)
            )->will($this->returnValue($o_set));
        $o_set->expects($this->at(1))->method('doFilter')
            ->with(
                $this->equalTo('Tox\\Application\\Model\\Set::filterEquals'),
                $this->equalTo($s_attr),
                $this->equalTo($f_val2)
            )->will($this->returnValue($o_set));
        $o_set->expects($this->at(2))->method('doFilter')
            ->with(
                $this->equalTo('Tox\\Application\\Model\\Set::filterBetween'),
                $this->equalTo($s_attr),
                $this->equalTo(array($f_val2, $f_val1))
            )->will($this->returnValue($o_set));
        $this->assertSame($o_set, call_user_func(array($o_set, "filter{$s_attr}Between"), $f_val1, $f_val1));
        $this->assertSame($o_set, call_user_func(array($o_set, "filter{$s_attr}Between"), $f_val2));
        $this->assertSame($o_set, call_user_func(array($o_set, "filter{$s_attr}Between"), $f_val2, $f_val1));
    }

    /**
     * @depends testMagicFilterAndExclude
     */
    public function testExcludeBetweenConvertedToExcludeEqualsOnPoint()
    {
        $f_val1 = microtime(true);
        $f_val2 = microtime(true);
        $s_attr = sha1(microtime());
        $o_set = $this->getMockForAbstractClass(
            'Tox\\Application\\Model\\Set',
            array(),
            '',
            true,
            true,
            true,
            array('doFilter')
        );
        $o_set->expects($this->at(0))->method('doFilter')
            ->with(
                $this->equalTo('Tox\\Application\\Model\\Set::excludeEquals'),
                $this->equalTo($s_attr),
                $this->equalTo($f_val1)
            )->will($this->returnValue($o_set));
        $o_set->expects($this->at(1))->method('doFilter')
            ->with(
                $this->equalTo('Tox\\Application\\Model\\Set::excludeEquals'),
                $this->equalTo($s_attr),
                $this->equalTo($f_val2)
            )->will($this->returnValue($o_set));
        $o_set->expects($this->at(2))->method('doFilter')
            ->with(
                $this->equalTo('Tox\\Application\\Model\\Set::excludeBetween'),
                $this->equalTo($s_attr),
                $this->equalTo(array($f_val2, $f_val1))
            )->will($this->returnValue($o_set));
        $this->assertSame($o_set, call_user_func(array($o_set, "exclude{$s_attr}Between"), $f_val1, $f_val1));
        $this->assertSame($o_set, call_user_func(array($o_set, "exclude{$s_attr}Between"), $f_val2));
        $this->assertSame($o_set, call_user_func(array($o_set, "exclude{$s_attr}Between"), $f_val2, $f_val1));
    }

    /**
     * @depends testMagicFilterAndExclude
     * @dataProvider provideFiltersAndExcludes
     */
    public function testEachFilterCauseAClone($func, $func, $attr, $value)
    {
        $value = array_slice(func_get_args(), 3);
        $o_set1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $i_pos = ('filter' == substr($func, 0, 6)) ? 6 : 7;
        $s_func = substr($func, 0, $i_pos) . $attr . substr($func, $i_pos);
        $o_set2 = call_user_func_array(array($o_set1, $s_func), $value);
        $this->assertInstanceOf(get_class($o_set1), $o_set2);
        $this->assertNotSame($o_set1, $o_set2);
    }

    /**
     * @depends testEachFilterCauseAClone
     * @dataProvider provideFiltersAndExcludes
     * @expectedException Tox\Application\Model\AttributeFilteredException
     */
    public function testEachAttributeCouldFilterOrExcludeOnce($func, $func, $attr, $value)
    {
        $value = array_slice(func_get_args(), 3);
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        do {
            $a_another = $this->provideFiltersAndExcludes();
            $s_another = array_rand($a_another);
            $a_another = $a_another[$s_another];
            $s_another = $a_another[1];
        } while ($s_another == $func);
        $i_pos = ('filter' == substr($func, 0, 6)) ? 6 : 7;
        $s_func = substr($func, 0, $i_pos) . $attr . substr($func, $i_pos);
        $o_set = call_user_func_array(array($o_set, $s_func), $value);
        $value = array_slice($a_another, 3);
        $i_pos = ('filter' == substr($s_another, 0, 6)) ? 6 : 7;
        $s_func = substr($s_another, 0, $i_pos) . $attr . substr($s_another, $i_pos);
        call_user_func_array(array($o_set, $s_func), $value);
    }

    /**
     * @depends testEachAttributeCouldFilterOrExcludeOnce
     * @dataProvider provideFiltersAndExcludes
     */
    public function testDuplicateFilterCauseNothing($func, $func, $attr, $value)
    {
        $value = array_slice(func_get_args(), 3);
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $i_pos = ('filter' == substr($func, 0, 6)) ? 6 : 7;
        $s_func = substr($func, 0, $i_pos) . $attr . substr($func, $i_pos);
        $o_set = call_user_func_array(array($o_set, $s_func), $value);
        $this->assertSame($o_set, call_user_func_array(array($o_set, $s_func), $value));
    }

    public function testMagicSort()
    {
        $s_attr = sha1(microtime());
        $c_val = (rand(0, 999) > 499) ? Set::SORT_ASC : Set::SORT_DESC;
        $o_set = $this->getMockForAbstractClass(
            'Tox\\Application\\Model\\Set',
            array(),
            '',
            true,
            true,
            true,
            array('doSort')
        );
        $o_set->expects($this->once())->method('doSort')
            ->with($this->equalTo($s_attr), $this->equalTo($c_val));
        call_user_func(array($o_set, 'sort' . $s_attr), $c_val);
    }

    /**
     * @depends testMagicSort
     */
    public function testEachSortCauseAClone()
    {
        $o_set1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $o_set2 = call_user_func(array($o_set1, 'sort' . sha1(microtime())));
        $this->assertInstanceOf(get_class($o_set1), $o_set2);
        $this->assertNotSame($o_set1, $o_set2);
    }

    /**
     * @depends testEachSortCauseAClone
     * @expectedException Tox\Application\Model\AttributeSortedException
     */
    public function testEachAttributeCouldSortOnce()
    {
        $s_attr = sha1(microtime());
        $c_val1 = (rand(0, 999) > 499) ? Set::SORT_ASC : Set::SORT_DESC;
        $c_val2 = (Set::SORT_ASC == $c_val1) ? Set::SORT_DESC : Set::SORT_ASC;
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $o_set = call_user_func(array($o_set, 'sort' . $s_attr), $c_val1);
        call_user_func(array($o_set, 'sort' . $s_attr), $c_val2);
    }

    /**
     * @depends testEachAttributeCouldSortOnce
     */
    public function testDuplicateSortCauseNothing()
    {
        $s_attr = sha1(microtime());
        $c_val = (rand(0, 999) > 499) ? Set::SORT_ASC : Set::SORT_DESC;
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $o_set = call_user_func(array($o_set, 'sort' . $s_attr), $c_val);
        $this->assertSame($o_set, call_user_func(array($o_set, 'sort' . $s_attr), $c_val));
    }

    public function testEachCropCauseAClone()
    {
        $o_set1 = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $o_set2 = $o_set1->crop(rand(), rand());
        $this->assertInstanceOf(get_class($o_set1), $o_set2);
        $this->assertNotSame($o_set1, $o_set2);
    }

    /**
     * @depends testEachCropCauseAClone
     */
    public function testInvalidCropCauseNothing()
    {
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $this->assertSame($o_set, $o_set->crop(0));
        $this->assertSame($o_set, $o_set->crop(0 - rand(), 0 - rand()));
    }

    /**
     * @depends testMagicFilterAndExclude
     * @depends testMagicSort
     * @expectedException Tox\Application\Model\IllegalFilterException
     */
    public function testUnexpectedMagicMethodsForbidden()
    {
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        call_user_func(array($o_set, 'f' . sha1(microtime())));
    }

    /**
     * @depends testDuplicateFilterCauseNothing
     * @depends testInvalidCropCauseNothing
     */
    public function testLazyReadingDao()
    {
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $o_set->expects($this->never())->method('getDefaultDao');
        $a_filters = array();
        foreach ($this->provideFiltersAndExcludes() as $ii) {
            if (rand(0, 999) > 499) {
                $i_pos = ('filter' == substr($ii[1], 0, 6)) ? 6 : 7;
                $s_func = substr($ii[1], 0, $i_pos) . $ii[2] . substr($ii[1], $i_pos);
                $a_args = array_slice($ii, 3);
                $o_set = call_user_func_array(array($o_set, $s_func), $a_args);
                $a_filters[$ii[2]] = array($ii[0], (1 == count($a_args)) ? $a_args[0] : $a_args);
            }
        }
        $o_set = $o_set->crop(rand(), rand());
    }

    /**
     * @depends testLazyReadingDao
     */
    public function testCountDirectlyWithoutLoading()
    {
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $a_filters = array();
        foreach ($this->provideFiltersAndExcludes() as $ii) {
            if (rand(0, 999) > 499) {
                $i_pos = ('filter' == substr($ii[1], 0, 6)) ? 6 : 7;
                $s_func = substr($ii[1], 0, $i_pos) . $ii[2] . substr($ii[1], $i_pos);
                $a_args = array_slice($ii, 3);
                $o_set = call_user_func_array(array($o_set, $s_func), $a_args);
                $a_filters[$ii[2]] = array($ii[0], (1 == count($a_args)) ? $a_args[0] : $a_args);
            }
        }
        $i_offset = rand(0, 999);
        $i_limit = rand(0, 999);
        $i_size = rand(0, 999);
        $o_set = $o_set->crop($i_offset, $i_limit);
        $o_set->expects($this->once())->method('getDefaultDao')
            ->will($this->returnValue($this->dao));
        $this->dao->expects($this->once())->method('countBy')
            ->with($this->equalTo($a_filters), $this->equalTo($i_offset), $this->equalTo($i_limit))
            ->will($this->returnValue($i_size));
        $this->dao->expects($this->never())->method('listBy');
        $this->assertEquals($i_size, count($o_set));
    }

    /**
     * @depends testLazyReadingDao
     */
    public function testLoadDirectlyWithoutCounting()
    {
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $a_filters = array();
        foreach ($this->provideFiltersAndExcludes() as $ii) {
            if (rand(0, 999) > 499) {
                $i_pos = ('filter' == substr($ii[1], 0, 6)) ? 6 : 7;
                $s_func = substr($ii[1], 0, $i_pos) . $ii[2] . substr($ii[1], $i_pos);
                $a_args = array_slice($ii, 3);
                $o_set = call_user_func_array(array($o_set, $s_func), $a_args);
                $a_filters[$ii[2]] = array($ii[0], (1 == count($a_args)) ? $a_args[0] : $a_args);
            }
        }
        $a_orders = array();
        for ($ii = 0, $jj = rand(1, 9); $ii < $jj; $ii++) {
            $c_order = (rand(0, 999) > 499) ? Set::SORT_ASC : Set::SORT_DESC;
            $s_attr = sha1(microtime());
            $o_set = call_user_func(array($o_set, 'sort' . $s_attr), $c_order);
            $a_orders[$s_attr] = $c_order;
        }
        $i_offset = rand(0, 999);
        $i_limit = rand(0, 999);
        $o_set = $o_set->crop($i_offset, $i_limit);
        $o_set->expects($this->once())->method('getDefaultDao')
            ->will($this->returnValue($this->dao));
        $a_rows = array();
        $this->dao->expects($this->once())->method('listBy')
            ->with(
                $this->equalTo($a_filters),
                $this->equalTo($a_orders),
                $this->equalTo($i_offset),
                $this->equalTo($i_limit)
            )->will($this->returnValue($a_rows));
        $this->dao->expects($this->never())->method('countBy');
        $o_set->rewind();
        $this->assertEquals(0, count($o_set));
    }

    /**
     * @depends testLoadDirectlyWithoutCounting
     * @expectedException Tox\Application\Model\SetWithoutFiltersException
     */
    public function testFiltersRequired()
    {
        $this->getMockForAbstractClass('Tox\\Application\\Model\\Set')->rewind();
    }

    /**
     * @depends testFiltersRequired
     */
    public function testGenerateAllIncludedModelsEntitiesOnInit()
    {
        $a_rows = array();
        for ($ii = 0, $i_times = rand(1, 9); $ii < $i_times; $ii++) {
            $kk = array('id' => sha1($ii . microtime()));
            $a_rows[] = $kk;
        }
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->staticExpects($this->exactly($i_times))->method('import')
            ->with($this->equalTo($o_set), $this->equalTo($this->dao))
            ->will($this->returnValue($o_mod));
        $o_set->expects($this->exactly($i_times))->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->with($this->equalTo(array()), $this->equalTo(array()), $this->equalTo(0), $this->equalTo(999))
            ->will($this->returnValue($a_rows));
        $o_set->rewind();
    }

    /**
     * @depends testGenerateAllIncludedModelsEntitiesOnInit
     */
    public function testIteration()
    {
        $a_rows = array();
        for ($ii = 0, $i_times = rand(1, 9); $ii < $i_times; $ii++) {
            $kk = array('id' => sha1($ii . microtime()));
            $a_rows[] = $kk;
        }
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->staticExpects($this->exactly($i_times))->method('import')
            ->with($this->equalTo($o_set), $this->equalTo($this->dao))
            ->will($this->returnValue($o_mod));
        $o_set->expects($this->exactly($i_times))->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->with($this->equalTo(array()), $this->equalTo(array()), $this->equalTo(0), $this->equalTo(999))
            ->will($this->returnValue($a_rows));
        $i_counter = 0;
        foreach ($o_set as $ii => $jj) {
            $this->assertSame($o_mod, $jj);
            $this->assertEquals($i_counter++, $ii);
        }
        $this->assertEquals($i_times, $i_counter);
    }

    public function testGetParent()
    {
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $this->assertNull($o_set->getParent());
        $this->assertFalse($o_set->hasParent());
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array($o_mod));
        $this->assertSame($o_mod, $o_set->getParent());
        $this->assertTrue($o_set->hasParent());
    }

    /**
     * @depends testCountDirectlyWithoutLoading
     */
    public function testGetLength()
    {
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $i_size = rand(0, 999);
        $o_set->expects($this->once())->method('getDefaultDao')
            ->will($this->returnValue($this->dao));
        $this->dao->expects($this->once())->method('countBy')
            ->with($this->equalTo(array()), $this->equalTo(0), $this->equalTo(0))
            ->will($this->returnValue($i_size));
        $this->assertEquals($i_size, $o_set->getLength());
    }

    public function testMagicMethods()
    {
        $o_set = $this->getMockForAbstractClass(
            'Tox\\Application\\Model\\Set',
            array(),
            '',
            true,
            true,
            true,
            array('getParent', 'getLength')
        );
        $s_val = microtime();
        $o_set->expects($this->once())->method('getParent')
            ->will($this->returnValue($s_val));
        $o_set->expects($this->once())->method('getLength')
            ->will($this->returnValue($s_val));
        $this->assertEquals($s_val, $o_set->parent);
        $this->assertEquals($s_val, $o_set->length);
    }

    /**
     * @depends testLoadDirectlyWithoutCounting
     */
    public function testCommitRecursively()
    {
        $a_rows = array(
            array('id' => microtime())
        );
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->staticExpects($this->once())->method('import')
            ->will($this->returnValue($o_mod));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_set->expects($this->once())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue($a_rows));
        $o_set->rewind();
        $o_mod->expects($this->once())->method('commit')
            ->will($this->returnValue($o_mod));
        $this->assertSame($o_set, $o_set->commit());
    }

    /**
     * @depends testCountDirectlyWithoutLoading
     */
    public function testAppendBufferedBeforeCommit()
    {
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_set->expects($this->once())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array()));
        $this->assertFalse($o_set->isChanged());
        $this->assertSame($o_set, $o_set->append($o_mod));
        $this->assertTrue($o_set->isChanged());
        $this->assertFalse($o_set->has($o_mod));
        $this->assertEquals(0, count($o_set));
    }

    /**
     * @depends testAppendBufferedBeforeCommit
     * @expectedException Tox\Application\Model\IllegalEntityForSetException
     */
    public function testAppendCorrespondingModelsOnly()
    {
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set')->crop(0, 999);
        $o_set->expects($this->once())->method('getModelClass')
            ->will($this->returnValue('Foo'));
        $o_set->append($o_mod);
    }

    /**
     * @depends testAppendBufferedBeforeCommit
     * @depends testLoadDirectlyWithoutCounting
     * @expectedException Tox\Application\Model\ModelIncludedInSetException
     */
    public function testAppendFailureForIncludedEntity()
    {
        $s_id = microtime();
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->staticExpects($this->once())->method('import')
            ->will($this->returnValue($o_mod));
        $o_mod->expects($this->atLeastOnce())->method('getId')
            ->will($this->returnValue($s_id));
        $o_mod->expects($this->once())->method('isAlive')
            ->will($this->returnValue(true));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_set->expects($this->atLeastOnce())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array(array('id' => $s_id))));
        $o_set->append($o_mod);
    }

    /**
     * @depends testAppendBufferedBeforeCommit
     * @depends testLoadDirectlyWithoutCounting
     */
    public function testAppendOnCommit()
    {
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->expects($this->once())->method('commit')
            ->will($this->returnValue($o_mod));
        $o_mod->expects($this->exactly(2))->method('isAlive')
            ->will($this->returnValue(true));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_set->expects($this->once())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array()));
        $this->assertFalse($o_set->isChanged());
        $o_set->append($o_mod)->commit();
        $this->assertFalse($o_set->isChanged());
        $this->assertTrue($o_set->has($o_mod));
        $this->assertEquals(1, count($o_set));
        $this->assertSame($o_mod, $o_set->current());
    }

    /**
     * @depends testCountDirectlyWithoutLoading
     */
    public function testDropBufferedBeforeCommit()
    {
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->staticExpects($this->once())->method('import')
            ->will($this->returnValue($o_mod));
        $o_mod->expects($this->exactly(2))->method('isAlive')
            ->will($this->returnValue(true));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_set->expects($this->atLeastOnce())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array(array('id' => microtime()))));
        $this->assertFalse($o_set->isChanged());
        $this->assertSame($o_set, $o_set->drop($o_mod));
        $this->assertTrue($o_set->isChanged());
        $this->assertTrue($o_set->has($o_mod));
        $this->assertEquals(1, count($o_set));
    }

    /**
     * @depends testDropBufferedBeforeCommit
     * @expectedException Tox\Application\Model\IllegalEntityForSetException
     */
    public function testDropCorrespondingModelsOnly()
    {
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set')->crop(0, 999);
        $o_set->expects($this->once())->method('getModelClass')
            ->will($this->returnValue('Foo'));
        $o_set->drop($o_mod);
    }

    /**
     * @depends testDropBufferedBeforeCommit
     * @depends testLoadDirectlyWithoutCounting
     * @expectedException Tox\Application\Model\PreparedModelToDropException
     */
    public function testDropFailureForPreparedEntity()
    {
        $s_id = microtime();
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->expects($this->once())->method('isAlive')
            ->will($this->returnValue(false));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_set->expects($this->atLeastOnce())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array()));
        $o_set->drop($o_mod);
    }

    /**
     * @depends testDropBufferedBeforeCommit
     * @depends testLoadDirectlyWithoutCounting
     */
    public function testDropOnCommit()
    {
        $s_id = microtime();
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->staticExpects($this->once())->method('import')
            ->will($this->returnValue($o_mod));
        $o_mod->expects($this->atLeastOnce())->method('getId')
            ->will($this->returnValue($s_id));
        $o_mod->expects($this->exactly(2))->method('isAlive')
            ->will($this->returnValue(true));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_set->expects($this->atLeastOnce())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array(array('id' => $s_id))));
        $this->assertFalse($o_set->isChanged());
        $o_set->drop($o_mod)->commit();
        $this->assertFalse($o_set->isChanged());
        $this->assertFalse($o_set->has($o_mod));
        $this->assertEquals(0, count($o_set));
    }

    public function testClear()
    {
        $s_id = microtime();
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->staticExpects($this->atLeastOnce())->method('import')
            ->will($this->returnValue($o_mod));
        $o_mod->expects($this->atLeastOnce())->method('getId')
            ->will($this->returnValue($s_id));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))->crop(0, 999);
        $o_set->expects($this->atLeastOnce())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array(array('id' => $s_id))));
        $this->assertFalse($o_set->isChanged());
        $this->assertSame($o_set, $o_set->clear());
        $this->assertTrue($o_set->isChanged());
        $this->assertEquals(1, count($o_set));
        $o_set->commit();
        $this->assertFalse($o_set->isChanged());
        $this->assertEquals(0, count($o_set));
    }

    public function testEnableAndDisableAsyncMode()
    {
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set');
        $this->assertTrue($o_set->isAsync());
        $this->assertSame($o_set, $o_set->disableAsync());
        $this->assertFalse($o_set->isAsync());
        $this->assertSame($o_set, $o_set->enableAsync());
        $this->assertTrue($o_set->isAsync());
    }

    /**
     * @depends testAppendOnCommit
     * @depends testEnableAndDisableAsyncMode
     */
    public function testAppendImmediatelyInSyncMode()
    {
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->expects($this->once())->method('commit')
            ->will($this->returnValue($o_mod));
        $o_mod->expects($this->exactly(2))->method('isAlive')
            ->will($this->returnValue(true));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))
            ->disableAsync()
            ->crop(0, 999);
        $o_set->expects($this->once())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array()));
        $this->assertFalse($o_set->isChanged());
        $o_set->append($o_mod);
        $this->assertFalse($o_set->isChanged());
        $this->assertTrue($o_set->has($o_mod));
        $this->assertEquals(1, count($o_set));
        $this->assertSame($o_mod, $o_set->current());
    }

    /**
     * @depends testDropOnCommit
     * @depends testEnableAndDisableAsyncMode
     */
    public function testDropImmediatelyInSyncMode()
    {
        $s_id = microtime();
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->staticExpects($this->once())->method('import')
            ->will($this->returnValue($o_mod));
        $o_mod->expects($this->atLeastOnce())->method('getId')
            ->will($this->returnValue($s_id));
        $o_mod->expects($this->exactly(2))->method('isAlive')
            ->will($this->returnValue(true));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))
            ->disableAsync()
            ->crop(0, 999);
        $o_set->expects($this->atLeastOnce())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array(array('id' => $s_id))));
        $this->assertFalse($o_set->isChanged());
        $o_set->drop($o_mod);
        $this->assertFalse($o_set->isChanged());
        $this->assertFalse($o_set->has($o_mod));
        $this->assertEquals(0, count($o_set));
    }

    /**
     * @depends testClear
     * @depends testEnableAndDisableAsyncMode
     */
    public function testClearImmediatelyInSyncMode()
    {
        $s_id = microtime();
        $o_mod = $this->getMock('Tox\\Application\\IModel');
        $o_mod->staticExpects($this->atLeastOnce())->method('import')
            ->will($this->returnValue($o_mod));
        $o_mod->expects($this->atLeastOnce())->method('getId')
            ->will($this->returnValue($s_id));
        $o_set = $this->getMockForAbstractClass('Tox\\Application\\Model\\Set', array(null, $this->dao))
            ->disableAsync()
            ->crop(0, 999);
        $o_set->expects($this->atLeastOnce())->method('getModelClass')
            ->will($this->returnValue(get_class($o_mod)));
        $this->dao->expects($this->once())->method('listBy')
            ->will($this->returnValue(array(array('id' => $s_id))));
        $this->assertFalse($o_set->isChanged());
        $this->assertSame($o_set, $o_set->clear());
        $this->assertFalse($o_set->isChanged());
        $this->assertEquals(0, count($o_set));
    }

    public function provideFiltersAndExcludes()
    {
        return array(
            array('filterEquals', 'filterEquals', sha1(microtime()), sha1(microtime())),
            array('filterGreaterThan', 'filterGreaterThan', sha1(microtime()), sha1(microtime())),
            array('filterGreaterOrEquals', 'filterGreaterOrEquals', sha1(microtime()), sha1(microtime())),
            array('filterLessThan', 'filterLessThan', sha1(microtime()), sha1(microtime())),
            array('filterLessOrEquals', 'filterLessOrEquals', sha1(microtime()), sha1(microtime())),
            array('filterBetween', 'filterBetween', sha1(microtime()), sha1(microtime()), sha1(microtime(true))),
            array('filterIn', 'filterIn', sha1(microtime()), array(sha1(microtime()))),
            array('filterLike', 'filterLike', sha1(microtime()), sha1(microtime())),
            array('excludeEquals', 'excludeEquals', sha1(microtime()), sha1(microtime())),
            array('filterLessOrEquals', 'excludeGreaterThan', sha1(microtime()), sha1(microtime())),
            array('filterLessThan', 'excludeGreaterOrEquals', sha1(microtime()), sha1(microtime())),
            array('filterGreaterOrEquals', 'excludeLessThan', sha1(microtime()), sha1(microtime())),
            array('filterGreaterThan', 'excludeLessOrEquals', sha1(microtime()), sha1(microtime())),
            array('excludeBetween', 'excludeBetween', sha1(microtime()), sha1(microtime()), sha1(microtime(true))),
            array('excludeIn', 'excludeIn', sha1(microtime()), array(sha1(microtime()))),
            array('excludeLike', 'excludeLike', sha1(microtime()), sha1(microtime()))
        );
    }
}

abstract class SetDummy extends Set
{
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
