<?php
/**
 * Processes the bootstrap of `tox.core' on importing.
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
 * @package   tox.core
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012-2013 SZen.in
 * @license   GNU General Public License, version 3
 */

namespace Tox\Core;

if ('@bootstrap.php' == basename(__FILE__)) {
    require_once __DIR__ . '/assembly.php';
    require_once __DIR__ . '/classmanager.php';
    require_once __DIR__ . '/packagemanager.php';
    require_once __DIR__ . '/runtime.php';
    Runtime::import('tox.core', __DIR__);
} else {
    // Phar::mapPhar('tox.core');
    require_once 'phar://tox.core/assembly.php';
    require_once 'phar://tox.core/classmanager.php';
    require_once 'phar://tox.core/packagemanager.php';
    require_once 'phar://tox.core/tox.php';
    Runtime::import('tox.core', __FILE__);
}

Runtime::setUp();

Runtime::alias('Tox\\Core\\Assembly', 'Tox\\Assembly');
Runtime::alias('Tox\\Core\\Exception', 'Tox\\Exception');
Runtime::alias('Tox\\Core\\ISingleton', 'Tox\\ISingleton');

Runtime::alias('Tox\\Type\\Type', 'Tox\\Type');

Runtime::alias('Tox\\Application\\Application', 'Tox\\Application');

__HALT_COMPILER();

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
