<?php
/**
 * Defines an exception for writing to the closed output.
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

namespace Tox\Application\Output;

use Tox\Core;

/**
 * Be raised on writing to the closed output.
 *
 * **THIS CLASS CANNOT BE INHERITED.**
 *
 * @package tox.application.output
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
final class ClosedOutputException extends Core\Exception
{
    /**
     * {@inheritdoc}
     *
     * > Defined as `0x80010201`.
     */
    const CODE = 0x80010201;

    /**
     * {@inheritdoc}
     *
     * > Defined as `output is already closed`.
     */
    const MESSAGE = 'output is already closed';
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
