<?php
/**
 * Defines the essential behaviors of controllers.
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

namespace Tox\Application;

use ArrayAccess;

/**
 * Announces the essential behaviors of configurations.
 *
 * @package tox.application
 * @author  Trainxy Ho <trainxy@gmail.com>
 * @since   0.1.0-beta1
 */
interface IConfiguration extends ArrayAccess
{

    /**
     * CONSTRUCT FUNCTOIN
     *
     * @param string $path Path of php configuration file.
     */
    public function __construct($path);

    /**
     * Import php configuration file to global configs.
     *
     * @param string $path Path of php configuration file.
     * @return self
     */
    public function import($path);

    /**
     * Load a hash array of config to global configs.
     *
     * @param array $items A has array of config
     * @return self
     */
    public function load(array $items);

    /**
     * Set a configuration item to global configs.
     *
     * @param string $item  Key of configuration
     * @param mixed  $value Value of configuration
     * @return self
     */
    public function set($item, $value);

    /**
     * Export configurations by expr rule.
     *
     * @param string $expr     Expr rule to export
     * @param mixed  $defaults Default value
     */
    public function export($expr, $defaults = null);

    /**
     * Dump all configurations, contains imported, loaded, and seted.
     *
     * @return array
     */
    public function dump();
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
