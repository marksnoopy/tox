<?php
/**
 * Defines the key-value paired data source.
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

namespace Tox\Data\KV;

use Tox;

/**
 * Represents as the key-value paired data source.
 * 
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 * 
 * __*ALIAS*__ as `Tox\Data\KV`.
 *
 * @package tox.data.kv
 * @author  Qiang Fu <fuqiang007enter@gmail.com>
 */
abstract class KV extends Tox\Core\Assembly implements Tox\Data\IKV
{
    /**
     * Stores the solid prefix string of keys.
     * 
     * @var string
     */
    public $keyPrefix;

    /**
     * CONSTRUCT FUNCTION
     */
    public function __construct()
    {
        if ($this->keyPrefix === null)
            $this->keyPrefix = 'memcached';
    }

    /**
     * Generates the unique key for the store value.
     * 
     * @param  string $key A key identifying a value to be cached.
     * @return sring
     */
    protected function generateUniqueKey($key)
    {
        return md5($this->keyPrefix . $key);
    }

    /**
     * Retrieves a value from cache with a specified key.
     * 
     * @param  string $key a unique key identifying the cached value.
     * @return string 
     */
    public function get($key)
    {
        if (($value = $this->getValue($this->generateUniqueKey($key))) !== false) {
            $data = unserialize($value);
            if (!is_array($data)) {
                return false;
            } else {
                return $data[0];
            }
        }
        return false;
    }

    /**
     * Stores a value identified by a key in cache.
     *
     * @param  string  $key    the key identifying the value to be cached.
     * @param  string  $value  the value to be cached.
     * @param  integer $expire the number of seconds in which the cached value
     *                         will expire. 0 means never expire.
     * @return boolean         true if the value is successfully stored into
     *                         cache, false otherwise.
     */
    public function set($key, $val, $expire = 0)
    {
        $val = serialize(array($val));
        return $this->setValue($this->generateUniqueKey($key), $val, $expire);
    }

    /**
     * Deletes a value with the specified key from cache.
     * 
     * @param  string  $id the key of the value to be deleted.
     * @return boolean     if no error happens during deletion.
     */
    public function delete($key)
    {
        return $this->deleteValue($this->generateUniqueKey($key));
    }

    /**
     * Stores a value identified by a key in the specific type of cache.
     * 
     * **THIS METHOD MUST BE IMPLEMENTED.**
     * 
     * @param  string  $key    the key identifying the value to be cached.
     * @param  string  $value  the value to be cached.
     * @param  integer $expire the number of seconds in which the cached value
     *                         will expire. 0 means never expire.
     * @return void
     */
    abstract protected function setValue($key, $val, $expire = 0);
    
    /**
     * Retrieves a value from the specific type of cache with a specified key.
     * 
     * **THIS METHOD MUST BE IMPLEMENTED.**
     * 
     * @param  string $key the key identifying the value to be cached.
     * @return mixed
     */
    abstract protected function getValue($key);
    
    /**
     * Deletes a value with the specified key from cache
     * 
     * @param  string $key the key of the value to be deleted
     * @return boolean     if no error happens during deletion
     */
    abstract protected function deleteValue($key);
    
    /**
     * Deletes all values from cache.
     * 
     * @return boolean whether the flush operation was successful.
     */
    public function clear()
    {
        return $this->clearValues();
    }

    /**
     * Makes the value +1 that identified by a key in cache.
     *
     * @param  string  $key the key identifying the value to be cached
     * @return boolean      true if the value is successfully stored into cache,
     *                      false otherwise
     */
    public function increase($key)
    {
        //todo 
    }

    /**
     * Makes the value -1 that identified by a key in cache.
     * 
     * This is the implementation of the method declared in the parent class.
     *
     * @param  string $key the key identifying the value to be cached
     * @return boolean     true if the value is successfully stored into cache,
     *                     false otherwise
     */
    public function decrease($key)
    {
        //todo
    }

    /**
     * Inserts the value lasted cell that identified by a key in cache.
     * 
     * @param  string $key the key identifying the value to be cached.
     * @return array 
     */
    public function push($key)
    {
        //todo
    }

    /**
     * Deletes the value lasted cell that identified by a key in cache.
     * 
     * @param  string $key the key identifying the value to be cached.
     * @return array 
     */
    public function pop($key)
    {
        //todo
    }

    /**
     * Deletes the value first cell that identified by a key in cache.
     * 
     * @param  string $key the key identifying the value to be cached
     * @return array 
     */
    public function shift($key)
    {
        //todo
    }

    /**
     * Inserts the value first cell that identified by a key in cache.
     * 
     * @param  string $key the key identifying the value to be cached
     * @return array 
     */
    public function unshift($key)
    {
        //todo 
    }

    /**
     * Returns whether there is a cache entry with a specified key.
     * 
     * This method is required by the interface ArrayAccess.
     * 
     * @param  string $id a key identifying the cached value.
     * @return boolean
     */
    public function offsetExists($id)
    {
        return $this->get($id) !== false;
    }

    /**
     * Retrieves the value from cache with a specified key.
     * 
     * This method is required by the interface ArrayAccess.
     * 
     * @param  string $id a key identifying the cached value.
     * @return mixed 
     */
    public function offsetGet($id)
    {
        return $this->get($id);
    }

    /**
     * Stores the value identified by a key into cache.
     * 
     * If the cache already contains such a key, the existing value will be
     * replaced with the new ones. To add expiration and dependencies, use the set() method.
     * This method is required by the interface ArrayAccess.
     * 
     * @param string $id the key identifying the value to be cached.
     * @param mixed 
     */
    public function offsetSet($id, $value)
    {
        $this->set($id, $value);
    }

    /**
     * Deletes the value with the specified key from cache
     * 
     * This method is required by the interface ArrayAccess.
     * 
     * @param  string  $id the key of the value to be deleted
     * @return boolean     if no error happens during deletion
     */
    public function offsetUnset($id)
    {
        $this->delete($id);
    }

}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
