<?php
/**
 * Represents as a runtime classes manager.
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
 * @package    Tox
 * @subpackage Tox\Core
 * @author     Snakevil Zen <zsnakevil@gmail.com>
 * @copyright  © 2012 szen.in
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Core;

use Tox;

class ClassManager extends Tox\Assembly
{
    protected $classAs;

    protected $loaded;

    public function __construct()
    {
        $this->classAs =
        $this->loaded = array();
    }

    public function transform($class)
    {
        settype($class, 'string');
        if (array_key_exists($class, $this->loaded))
        {
            return $class;
        }
        if (array_key_exists($class, $this->classAs))
        {
            return $this->classAs[$class];
        }
        return $class;
    }

    public function treatAs($class, $classAs)
    {
        settype($class, 'string');
        settype($classAs, 'string');
        if (array_key_exists($class, $this->loaded))
        {
            return $this;
        }
        if (array_key_exists($classAs, $this->loaded))
        {
            return $this;
        }
        if (array_key_exists($class, $this->classAs))
        {
            return $this;
        }
        $this->classAs[$class] = $classAs;
        return $this;
    }

    public function register($class, $path)
    {
        settype($class, 'string');
        settype($path, 'string');
        if (!array_key_exists($class, $this->loaded))
        {
            $this->loaded[$class] = $path;
        }
        return $this;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
