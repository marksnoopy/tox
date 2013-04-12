<?php
/**
 * Defines the behaviors of clusters of extended PHP data objects.
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

use Tox\Data;

/**
 * Announces the behaviors of clusters of extended PHP data objects.
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
interface ICluster extends Data\IPdo
{
    /**
     * Represents a special weight to be calculated automatically.
     *
     * @var int
     */
    const WEIGHT_AUTO = 0;

    /**
     * Adds an extra data object as a shadow worker for queries.
     *
     * @param  Data\IPdo $slave  An extra data object which in-replication to
     *                           the master.
     * @param  float     $weight Using weight of the data object.
     * @return self
     */
    public function addSlave(Data\IPdo $slave, $weight = self::WEIGHT_AUTO);

    /**
     * CONSTRUCT FUNCTION
     *
     * @param Data\IPdo $master The main data object.
     */
    public function __construct(Data\IPdo $master);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
