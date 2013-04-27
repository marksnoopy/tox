<?php
/**
 * Defines an exception for aliasing an existant class.
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

namespace Tox\Application\Input;

use Tox\Core;

/**
 * the request key is not set.
 *
 * **THIS CLASS CANNOT BE INHERITED.**
 *
 * @package tox.type.simple
 * @author  Mark Snoopy <marksnoopy@gmail.com>
 * @since   0.2
 */
final class UnknownTypeException extends Core\Exception
{
    /**
     * {@inheritdoc}
     *
     * > Defined as `0x80020101`.
     */
    const CODE = 0x80020101;

    /**
     * {@inheritdoc}
     *
     * > Defined as `class '%class$s' already exists`.
     */
    const MESSAGE = 'class \'%class$s\' already exists';
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
