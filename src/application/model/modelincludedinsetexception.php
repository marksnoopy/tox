<?php
/**
 * Defines an exception for appending an already included entity to a set.
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

namespace Tox\Application\Model;

use Tox\Core;

/**
 * Be raised on appending an already included entity to a set.
 *
 * **THIS CLASS CANNOT BE INHERITED.**
 *
 * @package tox.application.model
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.2.0
 */
final class ModelIncludedInSetException extends Core\Exception
{
    /**
     * {@inheritdoc}
     *
     * > Defined as `0x8002070A`.
     */
    const CODE = 0x8002070A;

    /**
     * {@inheritdoc}
     *
     * > Defined as `entity already included in set`.
     */
    const MESSAGE = 'entity already included in set';
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
