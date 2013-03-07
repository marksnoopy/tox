<?php
/**
 * Defines the runtime classes manager.
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

use Tox;

/**
 * Represents as the runtime classes manager.
 *
 * @package tox.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ClassManager extends Tox\Assembly
{
    /**
     * Stores the aliases.
     *
     * @internal
     *
     * @var string[]
     */
    protected $aliases;

    /**
     * Stores loaded classes locations.
     *
     * @internal
     *
     * @var string[]
     */
    protected $loaded;

    /**
     * CONSTRUCT FUNCTION
     */
    public function __construct()
    {
        $this->aliases =
        $this->loaded = array();
    }

    /**
     * Transforms an alias to a real class.
     *
     * @param  string $alias Alias to be transformed.
     * @return string
     */
    public function transform($alias)
    {
        $alias = (string) $alias;
        if (!array_key_exists($alias, $this->loaded) && array_key_exists($alias, $this->aliases)) {
            return $this->aliases[$alias];
        }
        return $alias;
    }

    /**
     * Aliases a class.
     *
     * @param  string       $class An original class.
     * @param  string       $alias The Alias.
     * @return ClassManager
     */
    public function alias($class, $alias)
    {
        if (class_exists($alias, false)) {
            throw new ExistantClassToAliasException(array('class' => $alias));
        }
        if (array_key_exists($class, $this->aliases)) {
            $class = $this->aliases[$class];
        }
        $this->aliases[$alias] = $class;
        return $this;
    }

    /**
     * Registers the definition location of a loaded real class.
     *
     * @param  string       $class A loaded real class.
     * @param  string       $path  The path of definition file
     * @return ClassManager
     */
    public function register($class, $path)
    {
        $class = (string) $class;
        $path = (string) $path;
        if (array_key_exists($class, $this->loaded)) {
            return $this;
        }
        $this->loaded[$class] = $path;
        foreach ($this->aliases as $s_alias => $s_class) {
            if ($class == $s_class) {
                $this->loaded[$s_alias] = '';
                class_alias($class, $s_alias);
            }
        }
        return $this;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
