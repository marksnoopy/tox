<?php
/**
 * Processes the bootstrap of `tox.application' on importing.
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
 * @package   tox.application
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012-2013 SZen.in
 * @license   GNU General Public License, version 3
 */

Tox::alias('Tox\\Application\\Configuration\\Configuration', 'Tox\\Application\\Configuration');
Tox::alias('Tox\\Application\\Router\\Router', 'Tox\\Application\\Router');
Tox::alias('Tox\\Application\\Controller\\Controller', 'Tox\\Application\\Controller');
Tox::alias('Tox\\Application\\View\\View', 'Tox\\Application\\View');
Tox::alias('Tox\\Application\\Model\\Model', 'Tox\\Application\\Model');
Tox::alias('Tox\\Application\\Dao\\Dao', 'Tox\\Application\\Dao');
Tox::alias('Tox\\Application\\Output\\Output', 'Tox\\Application\\Output');

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
