<?php
/**
 * Defines the essential behaviors of key-value paired data sources.
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

namespace Tox\Data;

/**
 * Announces the essential behaviors of key-value paired data sources.
 *
 * @package tox.data
 * @author  Qiang Fu <fuqiang007enter@gmail.com>
 * @since   0.1.0-beta1
 */
interface IKV extends \ArrayAccess, ISource
{
    /**
     * Retrieves a value from cache with a specified key.
     *
     * @param  string $key a unique key identifying the cached value.
     * @return string
     */
    public function get($key);

    /**
     * Stores a value identified by a key in cache.
     *
     * @param  string  $key    the key identifying the value to be cached.
     * @param  string  $value  the value to be cached.
     * @param  integer $expire the number of seconds in which the cached value
     *                         will expire. 0 means never expire.
     * @return boolean         true if the value is successfully stored into
     *                         cache, false otherwise
     */
    public function set($key, $val, $expire);

    /**
     * Makes the value +1 that identified by a key in cache.
     *
     * This is the implementation of the method declared in the parent class.
     *
     * @param  string  $key the key identifying the value to be cached.
     * @return boolean      true if the value is successfully stored into cache,
     *                      false otherwise
     */
    public function increase($key);

    /**
     * Makes the value -1 that identified by a key in cache.
     *
     * This is the implementation of the method declared in the parent class.
     *
     * @param  string  $key the key identifying the value to be cached.
     * @return boolean      true if the value is successfully stored into cache,
     *                      false otherwise
     */
    public function decrease($key);

    /**
     * Inserts the value lasted cell that identified by a key in cache.
     *
     * @param  string $key the key identifying the value to be cached.
     * @return array
     */
    public function push($key);

    /**
     * Deletes the value lasted cell that identified by a key in cache.
     *
     * @param  string $key the key identifying the value to be cached.
     * @return array
     */
    public function pop($key);

    /**
     * Deletes the value first cell that identified by a key in cache.
     *
     * @param  string $key the key identifying the value to be cached.
     * @return array
     */
    public function shift($key);

    /**
     * Inserts the value first cell that identified by a key in cache.
     *
     * @param  string $key the key identifying the value to be cached.
     * @return array
     */
    public function unshift($key);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
