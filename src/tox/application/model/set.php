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
 * @copyright  © 2012 szen.in
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Application\Model;

use Tox;
use Tox\Application;
use Tox\Application\Type;

/**
 * Represents as a collection of entities.
 *
 * @package Tox\Application\Model
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class Set extends Tox\Assembly implements ICollection
{
    protected $_committing;

    protected $_cursor;

    protected $_dao;

    protected $_elements;

    protected $_filters;

    protected $_index;

    protected $_length;

    protected $_totalLength;

    protected $_maxLength;

    protected $_orders;

    protected $_offset;

    /**
     * Stores the changements.
     *
     * @internal
     *
     * @var Application\Model[][]
     */
    protected $_stack;

    public function append(IEntity $entity)
    {
        if (strlen($entity->id))
        {
            $this->valid();
            if (isset($this->_stack['drop'][$entity->id]))
            {
                unset($this->_stack['drop'][$entity->id]);
            }
            if (isset($this->_elements[$entity->id]))
            {
                return $this;
            }
            if (!isset($this->_stack['append'][$entity->id]))
            {
                $this->_stack['append'][$entity->id] = $entity;
            }
            return $this;
        }
        foreach ($this->_stack['drop'] as $s_id => $o_entity)
        {
            if ($o_entity === $entity)
            {
                unset($this->_stack['drop'][$s_id]);
            }
        }
        foreach ($this->_stack['append'] as $s_id => $o_entity)
        {
            if ($o_entity === $entity)
            {
                return $this;
            }
        }
        $s_id = md5(get_class($this) . microtime());
        $this->_stack['append'][$s_id] = $entity;
        return $this;
    }

    public function __clone()
    {
        $this->_committing = FALSE;
        $this->_cursor =
        $this->_length =
        $this->_totalLength = -1;
        $this->_elements =
        $this->_index = array();
        $this->_stack = array('append' => array(),
            'change' => array(),
            'drop' => array()
        );
    }

    public function clear()
    {
        $this->valid();
        $this->_stack['drop'] = $this->_elements;
        return $this;
    }

    public function __construct(Application\IDao $dao = NULL)
    {
        if (NULL === $dao)
        {
            $dao = $this->getDefaultDao();
        }
        $this->_committing = FALSE;
        $this->_cursor =
        $this->_totalLength = -1;
        $this->_dao = $dao;
        $this->_elements =
        $this->_filters =
        $this->_index =
        $this->_orders = array();
        $this->_maxLength =
        $this->_offset = 0;
        $this->_stack = array('append' => array(),
            'change' => array(),
            'drop' => array()
        );
    }

    public function commit()
    {
        if (!$this->isChanged() || $this->_committing)
        {
            return $this;
        }
        $this->_committing = TRUE;
        foreach ($this->_stack['append'] as $o_entity)
        {
            $o_entity->commit();
        }
        foreach ($this->_stack['change'] as $o_entity)
        {
            $o_entity->commit();
        }
        foreach ($this->_stack['drop'] as $o_entity)
        {
            $o_entity->commit();
        }
        $this->_committing = FALSE;
        $this->_stack = array();
        return $this;
    }

    /**
     * Sets up an element entity through attributes.
     *
     * <code>
     * return Model::import($this);
     * </code>
     *
     * @return Application\Model
     */
    abstract protected function convertElement();

    public function count()
    {
        $this->valid();
        return $this->_totalLength;
    }

    public function crop($offset, $length = 0)
    {
        $offset = (int) $offset;
        $length = (int) $length;
        if (0 > $offset)
        {
            $offset = 0;
        }
        if (0 > $length)
        {
            $length = 0;
        }
        $o_collection = clone $this;
        $o_collection->_offset = $offset;
        $o_collection->_maxLength = $length;
        return $o_collection;
    }

    public function current()
    {
        if (!$this->valid())
        {
            return;
        }
        if (!$this->_cursor)
        {
            $this->load();
        }
        if (!$this->_elements[$this->_index[$this->_cursor]] instanceof Application\Model)
        {
            $this->_elements[$this->_index[$this->_cursor]] = $this->convertElement();
        }
        if (!$this->_elements[$this->_index[$this->_cursor]] instanceof Application\Model)
        {
            throw new ElementEntityConversionFailedException;
        }
        return $this->_elements[$this->_index[$this->_cursor]];
    }

    final protected function doFilter($attribute, $value)
    {
        if (is_array($value) || $value instanceof self)
        {
            return $this->doFilterIn($attribute, $value);
        }
        return $this->doFilterEqual($attribute, $value);
    }

    final protected function doFilterBetween($attribute, $minValue, $maxValue)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array(array((int) $minValue, (int) $maxValue), new Type\SetFilterType('><'));
        return $o_set;
    }

    final protected function doFilterEqual($attribute, $value)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array((string) $value, new Type\SetFilterType('='));
        return $o_set;
    }

    final protected function doFilterGreaterOrEqual($attribute, $value)
    {
        return $this->doFilterNotLessThan($attribute, $value);
    }

    final protected function doFilterGreaterThan($attribute, $value)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array((int) $value, new Type\SetFilterType('>'));
        return $o_set;
    }

    final protected function doFilterIn($attribute, $value)
    {
        if ($value instanceof self)
        {
            $value = $value->dumpIds();
        }
        else
        {
            $value = (array) $value;
        }
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array($value, new Type\SetFilterType('in'));
        return $o_set;
    }

    final protected function doFilterLessOrEqual($attribute, $value)
    {
        return $this->doFilterNotGreaterThan($attribute, $value);
    }

    final protected function doFilterLessThan($attribute, $value)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array((int) $value, new Type\SetFilterType('<'));
        return $o_set;
    }

    final protected function doFilterLike($attribute, $value)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array((string) $value, new Type\SetFilterType('%'));
        return $o_set;
    }

    final protected function doFilterNotBetween($attribute, $minValue, $maxValue)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array(array((int) $minValue, (int) $maxValue), new Type\SetFilterType('< >'));
        return $o_set;
    }

    final protected function doFilterNotEqual($attribute, $value)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array((string) $value, new Type\SetFilterType('!='));
        return $o_set;
    }

    final protected function doFilterNotGreaterThan($attribute, $value)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array((string) $value, new Type\SetFilterType('<='));
        return $o_set;
    }

    final protected function doFilterNotIn($attribute, $value)
    {
        if ($value instanceof self)
        {
            $value = $value->dumpIds();
        }
        else
        {
            $value = (array) $value;
        }
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array($value, new Type\SetFilterType('!in'));
        return $o_set;
    }

    final protected function doFilterNotLessThan($attribute, $value)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array((int) $value, new Type\SetFilterType('>='));
        return $o_set;
    }

    final protected function doFilterNotLike($attribute, $value)
    {
        $o_set = clone $this;
        $o_set->_filters[(string) $attribute] = array((string) $value, new Type\SetFilterType('!%'));
        return $o_set;
    }

    final protected function doFilterOut($attribute, $value)
    {
        if (is_array($value) || $value instanceof self)
        {
            return $this->doFilterNotIn($attribute, $value);
        }
        return $this->doFilterNotEqual($attribute, $value);
    }

    final protected function doSort($attribute, Type\SetSortOrder $order = NULL)
    {
        if (NULL === $order)
        {
            $order = new Type\SetSortOrder(NULL);
        }
        $o_set = clone $this;
        $o_set->_orders[(string) $attribute] = $order;
        return $o_set;
    }

    public function drop(IEntity $entity)
    {
        if (strlen($entity->id))
        {
            $this->valid();
            if (isset($this->_stack['append'][$entity->id]))
            {
                unset($this->_stack['append'][$entity->id]);
            }
            if (isset($this->_elements[$entity->id]))
            {
                return $this;
            }
            if (!isset($this->_stack['drop'][$entity->id]))
            {
                $this->_stack['drop'][$entity->id] = $entity;
            }
            return $this;
        }
        foreach ($this->_stack['append'] as $s_id => $o_entity)
        {
            if ($o_entity === $entity)
            {
                unset($this->_stack['append'][$s_id]);
            }
        }
        foreach ($this->_stack['drop'] as $s_id => $o_entity)
        {
            if ($o_entity === $entity)
            {
                return $this;
            }
        }
        $s_id = md5(get_class($this) . microtime());
        $this->_stack['drop'][$s_id] = $entity;
        return $this;
    }

    public function dumpIds()
    {
        $this->valid();
        return array_values($this->_index);
    }

    public function export()
    {
        if (!$this->valid())
        {
            return;
        }
        return $this->_elements[$this->_index[$this->_cursor]];
    }

    abstract protected function getDefaultDao();

    /**
     * Checks whether changed.
     *
     * @return bool
     */
    public function isChanged()
    {
        return !empty($this->_stack['append']) || !empty($this->_stack['drop']) || !empty($this->_stack['change']);
    }

    public function key()
    {
        if (!$this->valid())
        {
            return;
        }
        return $this->_index[$this->_cursor];
    }

    protected function load()
    {
        if (!$this->valid())
        {
            return self;
        }
        if (!$this->_length && 0 < $this->_totalLength)
        {
            $a_elements = call_user_func(array($this->_dao, 'listAndSortBy' . $s_func), $this->_filters, $this->_orders,
                $this->_offset, $this->_totalLength
            );
            for ($ii = 0, $jj = count($a_elements); $ii < $jj; $ii++)
            {
                $this->_elements[$a_elements[$ii]['id']] = $a_elements[$ii];
                $this->_index[] = $a_elements[$ii]['id'];
            }
        }
        return self;
    }

    public function next()
    {
        if ($this->valid())
        {
            $this->_cursor++;
        }
    }

    public function receiveChanging(Application\Model $entity)
    {
        if (!strlen($entity->id))
        {
            return $this;
        }
        $this->valid();
        if (!isset($this->_elements[$entity->id]))
        {
            return $this;
        }
        if (!isset($this->_stack['change'][$entity->id]))
        {
            $this->_stack['change'][$entity->id] = $entity;
        }
        return $this;
    }

    public function receiveResuming(Application\Model $entity)
    {
        if (!strlen($entity->id))
        {
            return $this;
        }
        $this->valid();
        if (!isset($this->_elements[$entity->id]))
        {
            return $this;
        }
        unset($this->_stack['change'][$entity->id]);
        return $this;
    }

    public function replace(self $collection)
    {
    }

    /**
     * Ignores all the changements.
     *
     * @return self
     */
    public function reset()
    {
        $this->_stack = array('append' => array(),
            'change' => array(),
            'drop' => array()
        );
        return $this;
    }

    public function rewind()
    {
        if (-1 == $this->_totalLength)
        {
            ksort($this->_filters);
            $s_func = implode('And', array_keys($this->_filters));
            $i_totalLength = call_user_func(array($this->_dao, 'countBy' . $s_func), $this->_filters) - $this->_offset;
            if ($this->_maxLength)
            {
                $i_totalLength = min($i_totalLength, $this->_maxLength);
            }
            $this->_length = 0;
            $this->_totalLength = $i_totalLength;
        }
        $this->_cursor = 0;
    }

    public function valid()
    {
        if (-1 == $this->_totalLength)
        {
            $this->rewind();
        }
        return -1 < $this->_cursor && $this->_cursor < $this->_totalLength;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
