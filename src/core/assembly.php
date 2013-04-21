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
 * @copyright © 2012-2013 PHP-Tox.org
 * @license   GNU General Public License, version 3
 */

namespace Tox\Core;

use ReflectionClass;
use ReflectionProperty;

/**
 * Represents as the root class of all components to provide essential
 * behaviors.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * __*ALIAS*__ as `Tox\Assembly`.
 *
 * @package tox.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
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
    const TOX_PROPERTY_DENIED = '';

    /**
     * Represents a magic property is read only.
     *
     * @internal
     */
    const TOX_PROPERTY_READONLY = 'r';

    /**
     * Represents a magic property is write only.
     *
     * @internal
     */
    const TOX_PROPERTY_WRITEONLY = 'w';

    /**
     * Represents a magic property is both readable and writable.
     *
     * @internal
     */
    const TOX_PROPERTY_PUBLIC = '*';

    /**
     * Stores detected magic readable and writable properties for each derived
     * component.
     *
     * @internal
     *
     * @var array
     */
    protected static $toxProperties = array();

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
        if (!$this->toxIsMagicPropReadable($prop)) {
            throw new PropertyReadDeniedException(array('object' => $this, 'property' => $prop));
        }
        $this->toxPreGet($prop);
        return call_user_func(array($this, 'toxGet' . $prop));
    }

    /**
     * Checks whether there is such a readable magic property.
     *
     * @internal
     *
     * @param  string $prop Name of a magic property
     * @return bool
     */
    protected function toxIsMagicPropReadable($prop)
    {
        $prop = (string) $prop;
        $a_props = $this->toxGetMagicProps();
        return isset($a_props[$prop]) &&
            (self::TOX_PROPERTY_READONLY == $a_props[$prop] || self::TOX_PROPERTY_PUBLIC == $a_props[$prop]);
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
    final protected function toxGetMagicProps()
    {
        $s_class = get_class($this);
        if (isset(self::$toxProperties[$s_class])) {
            return self::$toxProperties[$s_class];
        }
        self::$toxProperties[$s_class] = array();
        $o_rclass = new ReflectionClass($this);
        foreach ($o_rclass->getProperties() as $o_rprop) {
            if (!$o_rprop->isDefault() || !$o_rprop->isProtected() || $o_rprop->isStatic() ||
                0 === strpos($o_rprop->name, '_')
            ) {
                continue;
            }
            self::$toxProperties[$s_class][$o_rprop->name] = $this->toxDetectMagicProp($o_rprop);
        }
        return self::$toxProperties[$s_class];
    }

    /**
     * Detects whether a magic property readable and/or writable.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @param  ReflectionProperty $prop Magic property.
     * @return const
     */
    final protected function toxDetectMagicProp(ReflectionProperty $prop)
    {
        $o_rclass = $prop->getDeclaringClass();
        $s_getter = 'toxGet' . $prop->name;
        $b_getter = $o_rclass->hasMethod($s_getter) &&
            $o_rclass->getMethod($s_getter)->isProtected() &&
            !$o_rclass->getMethod($s_getter)->isStatic();
        $s_setter = 'toxSet' . $prop->name;
        $b_setter = $o_rclass->hasMethod($s_setter) &&
            $o_rclass->getMethod($s_setter)->isProtected() &&
            !$o_rclass->getMethod($s_setter)->isStatic();
        $i_ret = self::TOX_PROPERTY_DENIED;
        if ($b_getter) {
            $i_ret = self::TOX_PROPERTY_READONLY;
        }
        if ($b_setter) {
            $i_ret =
                self::TOX_PROPERTY_READONLY == $i_ret ?
                self::TOX_PROPERTY_PUBLIC :
                self::TOX_PROPERTY_WRITEONLY;
        }
        return $i_ret;
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
        if (!$this->toxIsMagicPropWritable($prop)) {
            throw new PropertyWriteDeniedException(array('object' => $this, 'property' => $prop));
        }
        call_user_func(array($this, 'toxSet' . $prop), $this->toxPreSet($prop, $value));
        $this->toxPostSet($prop);
    }

    /**
     * Checks whether there is such a writable magic property.
     *
     * @internal
     *
     * @param  string $prop Name of a magic property
     * @return bool
     */
    protected function toxIsMagicPropWritable($prop)
    {
        $prop = (string) $prop;
        $a_props = $this->toxGetMagicProps();
        return isset($a_props[$prop]) &&
            (self::TOX_PROPERTY_WRITEONLY == $a_props[$prop] || self::TOX_PROPERTY_PUBLIC == $a_props[$prop]);
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

    /**
     * Be invoked before retrieving a magic property.
     *
     * @param  string $prop Property name.
     * @return void
     */
    protected function toxPreGet($prop)
    {
    }

    /**
     * Be invoked before setting a magic property.
     *
     * NOTICE: The returning value WOULD be passed to setter methods.
     *
     * @param  string $prop  Property name.
     * @param  mixed  $value New value.
     * @return mixed
     */
    protected function toxPreSet($prop, $value)
    {
        return $value;
    }

    /**
     * Be invoked after setting a magic property.
     *
     * @param  string $prop Property name.
     * @return void
     */
    protected function toxPostSet($prop)
    {
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
