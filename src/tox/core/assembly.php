<?php
/**
 * Provides behaviors to all derived classes components.
 *
 * This class cannot be instantiated.
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
 * @package   Tox\Web
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox;

use ReflectionClass;
use ReflectionException;
use stdClass;

abstract class Assembly
{
    protected static $__properties;

    public function __get($prop)
    {
        $prop = (string) $prop;
        list($a_props) = array_values($this->__getProperties());
        if (!isset($a_props[$prop]))
        {
            throw new PropertyReadDeniedException(array('object' => $this, 'property' => $prop));
        }
        return call_user_func(array($this, '__get' . $prop));
    }

    final protected function __getProperties()
    {
        if (!isset(static::$__properties[get_class($this)]))
        {
            if (!is_array(static::$__properties))
            {
                static::$__properties = array();
            }
            static::$__properties[get_class($this)] = array('readable' => array(), 'writable' => array());
            $o_rclass = new ReflectionClass($this);
            foreach ($o_rclass->getProperties() as $o_rprop)
            {
                if (!$o_rprop->isDefault() || $o_rprop->isPublic() || $o_rprop->isStatic())
                {
                    continue;
                }
                try
                {
                    $o_rfunc = $o_rclass->getMethod('__get' . $o_rprop->name);
                    if (!$o_rfunc->isPublic() && !$o_rfunc->isStatic())
                    {
                        static::$__properties[get_class($this)]['readable'][$o_rprop->name] = $o_rprop->name;
                    }
                }
                catch (ReflectionException $ex)
                {
                }
                try
                {
                    $o_rfunc = $o_rclass->getMethod('__set' . $o_rprop->name);
                    if (!$o_rfunc->isPublic() && !$o_rfunc->isStatic())
                    {
                        static::$__properties[get_class($this)]['writable'][$o_rprop->name] = $o_rprop->name;
                    }
                }
                catch (ReflectionException $ex)
                {
                }
            }
        }
        return static::$__properties[get_class($this)];
    }

    public function __set($prop, $value)
    {
        $prop = (string) $prop;
        //list(, $a_props) = $this->__getProperties();
        list($a_props) = array_values($this->__getProperties());
        if (!isset($a_props[$prop]))
        {
            throw new PropertyWriteDeniedException(array('object' => $this, 'property' => $prop));
        }
        call_user_func(array($this, '__set' . $prop), $value);
    }

    public function __toString()
    {
        return get_class($this);
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
