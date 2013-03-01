<?php
/**
 * Defines the root class of all components to provide essential behaviors.
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

namespace Tox\Core;

use ReflectionClass;
use ReflectionException;

class_alias('Tox\\Core\\Assembly', 'Tox\\Assembly');

/**
 * Represents as the root class of all components to provide essential
 * behaviors.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * @package   tox.core
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class Assembly
{
    /**
     * Represents a magic property is untouchable.
     *
     * NOTICE: This constant is declared for compatibility, but SHOULD NOT be
     * used.
     *
     * @internal
     */
    const _TOX_PROPERTY_DENIED = '';

    /**
     * Represents a magic property is read only.
     *
     * @internal
     */
    const _TOX_PROPERTY_READONLY = 'r';

    /**
     * Represents a magic property is write only.
     *
     * @internal
     */
    const _TOX_PROPERTY_WRITEONLY = 'w';

    /**
     * Represents a magic property is both readable and writable.
     *
     * @internal
     */
    const _TOX_PROPERTY_PUBLIC = '*';

    /**
     * Stores detected magic readable and writable properties for each derived
     * component.
     *
     * @internal
     *
     * @var array
     */
    protected static $_tox_properties = array();

    /**
     * Be invoked on retrieving a magic property.
     *
     * 2 conditions are required to create a readable magic property:
     *
     * * An protected property with the same name;
     *
     * * An protected getter method of which name starts with '__get' and ends
     *   with the property name.
     *
     * For example,
     *
     *     protected $foo;
     *
     *     protected function __getFoo() {
     *         // codes here ...
     *     }
     *
     * @param  string $prop Name of a magic property.
     * @return mixed        Value of that magic property.
     */
    public function __get($prop)
    {
        if (!$this->_tox_isMagicPropReadable($prop)) {
            throw new PropertyReadDeniedException(array('object' => $this, 'property' => $prop));
        }
        return call_user_func(array($this, '__get' . $prop));
    }

    /**
     * Checks whether there is such a readable magic property.
     *
     * @internal
     *
     * @param  string $prop Name of a magic property
     * @return bool
     */
    protected function _tox_isMagicPropReadable($prop)
    {
        $prop = (string) $prop;
        $a_props = $this->_tox_getMagicProps();
        return isset($a_props[$prop]) &&
            (self::_TOX_PROPERTY_READONLY == $a_props[$prop] || self::_TOX_PROPERTY_PUBLIC == $a_props[$prop]);
    }

    /**
     * Retrieves the magic properties of the class type.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return array
     */
    final protected function _tox_getMagicProps()
    {
        $s_class = get_class($this);
        if (!isset(self::$_tox_properties[$s_class])) {
            self::$_tox_properties[$s_class] = array();
            $o_rclass = new ReflectionClass($this);
            foreach ($o_rclass->getProperties() as $o_rprop) {
                if (!$o_rprop->isDefault() || !$o_rprop->isProtected() || $o_rprop->isStatic() ||
                    0 === strpos($o_rprop->name, '_')
                ) {
                    continue;
                }
                try {
                    $o_rfunc = $o_rclass->getMethod('__get' . $o_rprop->name);
                    if ($o_rfunc->isProtected() && !$o_rfunc->isStatic()) {
                        self::$_tox_properties[$s_class][$o_rprop->name] = self::_TOX_PROPERTY_READONLY;
                    }
                } catch (ReflectionException $ex) {
                }
                try {
                    $o_rfunc = $o_rclass->getMethod('__set' . $o_rprop->name);
                    if ($o_rfunc->isProtected() && !$o_rfunc->isStatic()) {
                        self::$_tox_properties[$s_class][$o_rprop->name] =
                            isset(self::$_tox_properties[$s_class][$o_rprop->name]) ?
                            self::_TOX_PROPERTY_PUBLIC :
                            self::_TOX_PROPERTY_WRITEONLY;
                    }
                } catch (ReflectionException $ex) {
                }
            }
        }
        return self::$_tox_properties[$s_class];
    }

    /**
     * Be invoked on setting a magic property.
     *
     * 2 conditions are required to create a writable magic property:
     *
     * * An protected property with the same name;
     *
     * * An protected setter method of which name starts with '__set' and ends
     *   with the property name.
     *
     * For example,
     *
     *     protected $foo;
     *
     *     protected function __setFoo($value) {
     *         // codes here ...
     *     }
     *
     * @param  string $prop  Name of a magic property.
     * @param  mixed  $value The new value.
     * @return void
     */
    public function __set($prop, $value)
    {
        if (!$this->_tox_isMagicPropWritable($prop)) {
            throw new PropertyWriteDeniedException(array('object' => $this, 'property' => $prop));
        }
        call_user_func(array($this, '__set' . $prop), $value);
    }

    /**
     * Checks whether there is such a writable magic property.
     *
     * @internal
     *
     * @param  string $prop Name of a magic property
     * @return bool
     */
    protected function _tox_isMagicPropWritable($prop)
    {
        $prop = (string) $prop;
        $a_props = $this->_tox_getMagicProps();
        return isset($a_props[$prop]) &&
            (self::_TOX_PROPERTY_WRITEONLY == $a_props[$prop] || self::_TOX_PROPERTY_PUBLIC == $a_props[$prop]);
    }

    /**
     * Be invoked on string type casting.
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this);
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
