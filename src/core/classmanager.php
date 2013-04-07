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
 * @copyright © 2012-2013 PHP-Tox.org
 * @license   GNU General Public License, version 3
 */

namespace Tox\Core;

/**
 * Represents as the runtime classes manager.
 *
 * @package tox.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
class ClassManager extends Assembly
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
        return array_key_exists($alias, $this->aliases) ? $this->aliases[$alias] : $alias;
    }

    /**
     * Aliases a class.
     *
     * @param  string $class An original class.
     * @param  string $alias The Alias.
     * @return self
     *
     * @throws ExistantClassToAliasException If the alias is an existant class.
     */
    public function alias($class, $alias)
    {
        $class = (string) $class;
        $alias = (string) $alias;
        if (class_exists($alias, false)) {
            throw new ExistantClassToAliasException(array('class' => $alias));
        }
        if (array_key_exists($class, $this->aliases)) {
            $class = $this->aliases[$class];
        }
        $this->aliases[$alias] = $class;
        if (class_exists($class, false)) {
            if (!array_key_exists($class, $this->loaded)) {
                $this->loaded[$class] = '';
            }
            return $this->announce($alias);
        }
        return $this;
    }

    /**
     * Registers the definition location of a loaded real class.
     *
     * @param  string $class A loaded real class.
     * @param  string $path  The path of definition file
     * @return self
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
                $this->announce($s_alias);
            }
        }
        return $this;
    }

    /**
     * Announces an alias to the whole runtime enviroment.
     *
     * @param  string $alias A confirmed alias.
     * @return self
     */
    protected function announce($alias)
    {
        class_alias($this->aliases[$alias], $alias);
        $this->loaded[$alias] = '';
        return $this;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
