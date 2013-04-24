<?php
/**
 * Defines an exception for invoking unsupport features of clusters.
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

namespace Tox\Data\Pdo;

use Tox\Core;

/**
 * Be raised on invoking unsupport features of clusters.
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.2.0
 */
final class ClusterOopsException extends Core\Exception
{
    /**
     * {@inheritdoc}
     *
     * > Defined as `0x8004010E`.
     */
    const CODE = 0x8004010E;

    /**
     * {@inheritdoc}
     *
     * > Defined as `feature '%feature$s' not supported`.
     */
    const MESSAGE = 'feature \'%feature$s\' not supported';
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120