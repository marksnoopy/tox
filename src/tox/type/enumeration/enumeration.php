<?php
/**
 * Represents as a enumeration value.
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
 * @package    Tox\Application
 * @subpackage Model
 * @author     Snakevil Zen <zsnakevil@gmail.com>
 * @copyright  © 2013 szen.in
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Type\Enumeration;

use ReflectionClass;

use Tox\Core;

abstract class Enumeration extends Core\Assembly
{
    protected static $availableValues;

    protected $value;

    final public function __construct($value)
    {
        settype($value, 'string');
        if (!is_array(static::$availableValues) || !in_array($value, static::$availableValues))
        {
            throw new IllegalEnumerationValueException(array('value' => $value));
        }
        $this->value = $this->format($value);
    }

    protected function format($value)
    {
        return $value;
    }

    final protected function __getValue()
    {
        return $this->value;
    }

    final protected function __setValue($value)
    {
    }

    final public function __toString()
    {
        return $this->value;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
