<?php
/**
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
 * @copyright © 2012-2013 szen.in
 * @license   GNU Public License
 */

namespace Tox\Application\Model;

use Tox\Core;
use Tox\Application;

/**
 * Represents as an entity.
 *
 * Entities related to any others should have levels.
 *
 * Once to retrieve an single entity as an attribute of another, the retrieved
 * entity MUST be the up-level to the other.
 *
 * Once to retrieve an entity inside some collection as an attribute of another,
 * the retrieved entity MUST be the down-level to the other.
 *
 * Extra operations to modify the relationships SHOULD be contained in the
 * down-level entities, rather than up-levels.
 *
 * For compatible and secure reasons, changements of entities would NOT be
 * affected until a commit. For example,
 *
 * <code>
 * Model::setUp('foo')->name; // Foo
 * Model::setUp('foo')->name = 'Bar';
 * Model::setUp('foo')->name; // Foo
 * Model::setUp('foo')->commit()->name; // Bar
 * </code>
 *
 * The following are several examples to guide that HOWTO work on this
 * mechanism:
 *
 * - To create a new entity:
 *
 * <code>
 * Model::prepare(array(
 *     'id' => 'foo',
 *     'upLevel' => Model::prepare(array(
 *         'id' => 'bar',
 *         'title' => 'blah...'
 *     ))
 * ))->commit();
 * </code>
 *
 * Entity 'foo' and related up-level entity 'bar' would be committed to the
 * data source.
 *
 * - To modify an existant entity:
 *
 * <code>
 * Model::setUp('foo')->upLevel->title = 'new blah...';
 * Model::setUp('foo')->commit();
 * // Model::setUp('foo')->upLevel->commit();
 * </code>
 *
 * The committings from each entity which related to the entity 'bar' would
 * affect the changement.
 *
 * - To relace a related up-level entity to another:
 *
 * <code>
 * Model::setUp('foo')->upLevel = Model::prepare(array(
 *  'id' => 'new-bar',
 *  'title' => 'new blah...';
 * ));
 * Model::setup('foo')->upLevel->commit();
 * </code>
 *
 * For this behavior, Model::__setUpLevel() method COULD be defined in need:
 *
 * <code>
 * protected function __setUpLevel(Model $upLevel)
 * {
 *     // extra operations to modify the relationship are put here.
 * }
 * </code>
 *
 * NOTICE: The relationships dropped from the old up-level entity and created
 * to the new up-level SHOULD be implemented in the corresponding data
 * accessor.
 *
 * - To break the relationship to an up-level entity:
 *
 * WARNING: This purpose is CONFLICT to the relationship mechanism. Custom
 * methods RECOMMENDED.
 *
 * Or a dirty Model::__setUpLevel() method is REQUIRED like:
 *
 * <code>
 * protected function __setUpLevel(Model $upLevel = NULL)
 * {
 *     // extra operatoins here.
 * }
 * </code>
 *
 * In this way, the up-level entity could be avoid:
 *
 * <code>
 * Model::setUp('foo')->upLevel = NULL; // BREAK the relationship
 * </code>
 *
 * - To terminate an entity:
 *
 * As default, ALL down-level entities SHOULD be terminated too on terminating
 * their up-level entity.
 *
 * However, this stragety is NOT always ensured, such as any 'm:n' relationship.
 * So the terminating of down-level entities have to been added manually in
 * Model::onTermination() method.
 *
 * <code>
 * protected function onTermination()
 * {
 *     // terminates all down-level entities here.
 * }
 * </code>
 *
 * @package Tox\Application
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class Model extends Core\Assembly implements IEntity
{
    /**
     * Maps the names of properties as up-level attributes to the names of
     * properties as down-level collections attributes of the corresponding
     * up-level elements.
     *
     * Why this property is REQUIRED? For example, the following map announced:
     *
     * <code>
     * protected static $attributesMap = array(
     *     'upLevel' => 'downLevels'
     * );
     * </code>
     *
     * And then, entity 'foo' set entity 'bar' as its upLevel attribute.
     *
     * <code>
     * Model::prepare(array(
     *     'id' => 'foo'
     * ))->upLevel = Model::prepare(array(
     *     'id' => 'bar'
     * ));
     * </code>
     *
     * That means entity 'foo' SHOULD be able to retrieve as a down-level
     * element from entity 'bar' after committing.
     *
     * <code>
     * Model::setUp('bar')->downLevels->current() == Model::setUp('bar');
     * </code>
     *
     * That works for the following internal logic would be executed on the
     * setting process:
     *
     * <code>
     * Model::prepare(array(
     *     'id' => 'bar'
     * ))->downLevels->append(Model::prepare(array(
     *         'id' => 'foo'
     *     )));
     * </code>
     *
     * @var string[]
     */
    protected static $attributesMap = array();

    /**
     * Stores the committing status.
     *
     * @internal
     *
     * @var bool
     */
    protected $_committing;

    /**
     * Stores the data accessor.
     *
     * @internal
     *
     * @var IDao
     */
    protected $_dao;

    /**
     * Stores the indentifier.
     *
     * @internal
     *
     * @var string
     */
    protected $id;

    /**
     * Stores the instances for loaded entities.
     *
     * @var Model[]
     */
    protected static $_instances;

    /**
     * Stores the changements of attributes.
     *
     * @internal
     *
     * @var array
     */
    protected $_stack;

    /**
     * Stores the names of properties as attributes which are related to
     * up-level entities.
     *
     * Elements SHOULD obey the following syntax:
     *
     * <code>
     * string ENTITY-ID => string PROPERTY-NAME,
     * </code>
     *
     * @internal
     *
     * @var string[]
     */
    protected $_upLevels;

    /**
     * Marks the changement of the property.
     *
     * @internal
     *
     * @param  string $prop
     * @param  mixed  $value
     * @return self
     */
    protected function addChangement($prop, $value)
    {
        $b_changed = !$this->isChanged();
        $this->_stack[(string) $prop] = $value;
        if ($b_changed)
        {
            $this->notifyChangingToUpLevels();
        }
        return $this;
    }

    /**
     * Prepares a duplicate entity.
     */
    public function __clone()
    {
        $this->_committing = FALSE;
        $this->id = '';
        $this->_stack =
        $this->_upLevels = array();
    }

    /**
     * Commits all the changements.
     *
     * @return self
     */
    public function commit()
    {
        if (!$this->isChanged() || $this->_committing)
        {
            return $this;
        }
        $this->_committing = TRUE;
        $a_fields = $a_dlvls = array();
        reset($this->_stack);
        for ($ii = 0, $jj = count($this->_stack); $ii < $jj; $ii++)
        {
            list($s_attr) = each($this->_stack);
            if (NULL !== $this->$s_attr && $this->__get($s_attr) instanceof Set)
            {
                if (NULL === $this->_stack[$s_attr])
                {
                    $this->$s_attr->clear();
                }
                if ($this->_stack[$s_attr] instanceof self)
                {
                    $this->$s_attr->clear()->append($this->_stack[$s_attr]);
                }
                if ($this->_stack[$s_attr] instanceof Set)
                {
                    $this->$s_attr->replace($this->_stack[$s_attr]);
                }
                $a_dlvls[] = $this->$s_attr;
                continue;
            }
            if (NULL !== $this->$s_attr && $this->__get($s_attr) instanceof self || $this->_stack[$s_attr] instanceof self)
            {
                if (strlen($this->id) && NULL !== $this->$s_attr)
                {
                    if (isset(static::$attributesMap[$s_attr]))
                    {
                        $this->$s_attr->{static::$attributesMap[$s_attr]}->drop($this);
                    }
                    $this->$s_attr->commit();
                }
                if (NULL !== $this->_stack[$s_attr])
                {
                    if (isset(static::$attributesMap[$s_attr]))
                    {
                        $this->_stack[$s_attr]->{static::$attributesMap[$s_attr]}->append($this);
                    }
                    $this->_stack[$s_attr]->commit();
                }
                $this->$s_attr = $this->_stack[$s_attr];
            }
            $a_fields[$s_attr] = (string) $this->_stack[$s_attr];
        }
        if (strlen($this->id))
        {
            $this->_dao->update($this->id, $a_fields);
        }
        else
        {
            $this->id = $this->_dao->create($a_fields);
        }

        foreach ($a_fields as $k=>$v) {
            if (!($v  instanceof self)) {
                $this->$k = $v;
            }
        }
        for ($ii = 0, $jj = count($a_dlvls); $ii < $jj; $ii++)
        {
            $a_dlvls[$ii]->commit();
        }
        $this->_committing = FALSE;
        $this->_stack = array();
        return $this;
    }

    protected function __construct(Application\IDao $dao)
    {
        $this->_committing = FALSE;
        $this->_dao = $dao;
        $this->id = '';
        $this->_stack =
        $this->_upLevels = array();
    }

    /**
     * Retrieves the value of a magic readable property.
     *
     * @internal
     *
     * @param  string $prop
     * @return mixed
     */
    public function __get($prop)
    {
        $prop = (string) $prop;
        $m_ret = parent::__get($prop);
        if ($m_ret instanceof self && isset(static::$attributesMap[$prop]) && !isset($this->_upLevels[$prop]))
        {
            $this->_upLevels[$prop] = $m_ret;
        }
        if ($m_ret instanceof IModel && isset($this->_stack[$prop]))
        {
            $m_ret = $this->_stack[$prop];
        }
        return $m_ret;
    }

    /**
     * Retrieves the default data accessor.
     *
     * @return IDao
     */
    abstract protected static function getDefaultDao();

    /**
     * Retrieves the identifier.
     *
     * @internal
     *
     * @return string
     */
    protected function __getId()
    {
        return $this->id;
    }

    /**
     * Loads an entity from a collection.
     *
     * @param  ICollection $set
     * @param  IDao              $dao OPTIONAL. Data accessor used. NULL
     *                                defaults.
     * @return self
     */
    public static function import(ICollection $set, Application\IDao $dao = NULL)
    {
        if ($set->export() instanceof self)
        {
            return $set->export();
        }
        $o_entity = static::manufactor((array) $set->export(), $dao);
        return $o_entity;
    }

    /**
     * Checks whether changed.
     *
     * @return bool
     */
    public function isChanged()
    {
        return !empty($this->_stack);
    }

    /**
     * Sets up an entity through attributes.
     *
     * @internal
     *
     * @param  array $attributes
     * @param  IDao  $dao        OPTIONAL. Data accessor used. NULL defaults.
     * @return self
     */
    protected static function manufactor($attributes, Application\IDao $dao = NULL)
    {
        $attributes = (array) $attributes;
        if (NULL === $dao)
        {
            $dao = static::getDefaultDao();
        }
        $o_entity = new static($dao);
        list($a_rprops, $a_wprops) = array_values($o_entity->__getProperties());
        $b_prepared = !isset($attributes['id']);
        reset($attributes);
        for ($ii = 0, $jj = count($attributes); $ii < $jj; $ii++)
        {
            list($s_prop) = each($attributes);
            if (isset($a_rprops[$s_prop]) || isset($a_wprops[$s_prop]))
            {
                if ($b_prepared)
                {
                    $o_entity->_stack[$s_prop] = $attributes[$s_prop];
                }
                else
                {
                    $o_entity->$s_prop = $attributes[$s_prop];
                }
            }
        }
        if (!$b_prepared)
        {
            if (NULL === static::$_instances)
            {
                static::$_instances = array();
            }
            if (!isset(static::$_instances[get_called_class()]))
            {
                static::$_instances[get_called_class()] = array();
            }
            static::$_instances[get_called_class()][$attributes['id']] = $o_entity;
        }
        return $o_entity;
    }

    /**
     * Notifies first changement to all up-level entities.
     *
     * @internal
     *
     * @return self
     */
    protected function notifyChangingToUpLevels()
    {
        reset($this->_upLevels);
        for ($ii = 0, $jj = count($this->_upLevels); $ii < $jj; $ii++)
        {
            list($s_prop, $o_ulvl) = each($this->_upLevels);
            $o_ulvl->receiveChanging($this, static::$attributesMap[$s_prop]);
        }
    }

    /**
     * Notifies the resuming changement to all up-level entities.
     *
     * @internal
     *
     * @return self
     */
    protected function notifyResumingToUpLevels()
    {
        reset($this->_upLevels);
        for ($ii = 0, $jj = count($this->_upLevels); $ii < $jj; $ii++)
        {
            list($s_prop, $o_ulvl) = each($this->_upLevels);
            $o_ulvl->receiveResuming($this, static::$attributesMap[$s_prop]);
        }
    }

    /**
     * Prepares an entity.
     *
     * @param  mixed[] $attributes
     * @param  IDao    $dao        OPTIONAL. Data accessor used. NULL defaults.
     * @return self
     */
    public static function prepare($attributes, Application\IDao $dao = NULL)
    {
        $attributes = (array) $attributes;
        $s_id = '';
        if (isset($attributes['id']))
        {
            $s_id = $attributes['id'];
            unset($attributes['id']);
        }
        $o_entity = static::manufactor((array) $attributes, $dao);
        if (strlen($s_id))
        {
            $o_entity->_stack['id'] = $s_id;
        }
        return $o_entity;
    }

    /**
     * Receives the changement of an entity in a down-level collection.
     *
     * @param  self   $entity
     * @param  string $collection
     * @return self
     */
    public function receiveChanging(IEntity $entity, $collection)
    {
        $collection = (string) $collection;
        list($a_props) = $this->__getProperties();
        if (isset($a_props[$collection]))
        {
            $this->$collection->receiveChanging($entity);
            $this->addChangement($collection, $this->$collection);
        }
        return $this;
    }

    /**
     * Receives the resuming changement of an entity in a down-level collection.
     *
     * @param  self   $entity
     * @param  string $collection
     * @return self
     */
    public function receiveResuming(IEntity $entity, $collection)
    {
        $collection = (string) $collection;
        list($a_props) = $this->__getProperties();
        if (isset($a_props[$collection]))
        {
            $this->$collection->receiveResuming($entity);
            if (!$this->$collection->isChanged())
            {
                $this->removeChangement($collection);
            }
        }
        return $this;
    }

    public function relateTo()
    {
    }

    /**
     * Ignores the changement of a property.
     *
     * @internal
     *
     * @param  string $prop
     * @return self
     */
    protected function removeChangement($prop)
    {
        unset($this->_stack[$prop]);
        if (!$this->isChanged())
        {
            $this->notifyResumingToUpLevels();
        }
        return $this;
    }

    /**
     * Resets all changements.
     *
     * @return self
     */
    public function reset()
    {
        if (!strlen($this->id))
        {
            return $this;
        }
        if ($this->isChanged())
        {
            $this->_stack = array();
            $this->notifyResumingToUpLevels();
        }
        return $this;
    }

    /**
     * Sets the value of a magical writable property.
     *
     * @internal
     *
     * @param  string $prop
     * @param  mixed  $value
     * @return void
     */
    public function __set($prop, $value)
    {
        $prop = (string) $prop;
        //list(,$a_props) = $this->__getProperties();
        list($a_props) = array_values($this->__getProperties());
        if (isset($a_props[$prop]))
        {
            $m_old = $this->$prop;
        }
        parent::__set($prop, $value);
        $m_new = $this->$prop;
        $this->$prop = $m_old;
        if ($m_old === $m_new)
        {
            $this->removeChangement($prop);
            return;
        }
        $this->addChangement($prop, $m_new);
    }

    /**
     * Sets the identifier.
     *
     * @internal
     *
     * @param  string $value
     * @return void
     *
     * @throws IdentifierReadOnlyException
     */
    protected function __setId($value)
    {
        throw new IdentifierReadOnlyException(array('object' => $this));
    }

    /**
     * Loads an entity from the data source.
     *
     * @param  string $id
     * @param  IDao   $dao OPTIONAL. Data accessor used. NULL defaults.
     * @return self
     */
    public static function setUp($id, Application\IDao $dao = NULL)
    {
        $s_type = get_called_class();
        $id = (string) $id;
        if (isset(static::$_instances[$s_type][$id]))
        {
            return static::$_instances[$s_type][$id];
        }
        if (NULL === $dao)
        {
            $dao = static::getDefaultDao();
        }
        $a_props = $dao->read($id);
        if (!is_array($a_props))
        {
            throw new NonExistantEntityException(array('id' => $id, 'type' => $s_type));
        }
        $o_entity = static::manufactor($a_props, $dao);
        return $o_entity;
    }

    /**
     * Retrieves the indentifer on string casting.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->id;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
