<?php
/**
 * Defines the test case for Tox\Web\Response\Response.
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

namespace Tox\Type\Simple;

use Tox\Type;

/**
 * Represents as the boxable objects.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * **NOTICE: Boxed variables would not be set free on `unset()` statements,
 * until another more `Varbase::gc()` has invoked.**
 *
 * @property mixed $value Retrieves the internal scalar value.
 *
 * @package tox.type.email
 * @author  Mark Snoopy <marksnoopy@gmail.com>
 * @since   0.2.0
 */

class Email extends Type\Type
{
    /**
     * check the value is email type or not.
     * @param mixed $value
     * @return mixed
     * @throws UnexpectedTypeException
     */
    protected function validate($value)
    {
        $s_match = '/([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i';
        if (preg_match($s_match, $value)) {
            return $value;
        } else {
            throw new UnexpectedTypeException;
        }
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
