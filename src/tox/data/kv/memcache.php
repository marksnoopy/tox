<?php
/**
 * Defines the memcache data source.
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


/**
 * Represents as the memcache data source.
 *
 * @package tox.data.kv
 * @author  Qiang Fu <fuqiang007enter@gmail.com>
 * @since   0.1.0-beta1
 */
class Memcache extends KV
{
    /**
     * Choices of Memcache or Memcached .
     *
     * @var bool
     */
    public $useMemcached;

    /**
     * Memcache the Memcache instance.
     *
     * @var mixed
     */
    private $_cache;

    /**
     * Lists of memcache server configurations.
     *
     * @var array
     */
    private $_servers;

    /**
     * Memcache default expire time
     */
    private $_expireTime;

    /**
     * Memcache persistent id
     */
    private $_field;

    /**
     * Constructor.
     *
     */
    public function __construct($field = null)
    {
        if (null === $this->useMemcached) {
            $this->useMemcached = true;
        }
        if (null != $field) {
            $this->_field = $field;
        }
    }

    /**
     * Sets the memcache default expire time
     *
     * @param field $expire
     */
    public function setExpireTime($expire)
    {
        if (null !== $expire) {
            $this->_expireTime = $expire;
        }
    }

    /**
     * Gets the memcache default expire time
     *
     * @param field $expire
     */
    public function getExpireTime()
    {
        return $this->_expireTime;
    }

    /**
     * It creates the memcache instance and adds memcache servers.
     *
     * @return void
     */
    public function init()
    {
        $servers = $this->getServers();
        $cache = $this->getMemCache();
        if (count($servers)) {
            foreach ($servers as $server) {
                if ($this->useMemcached) {
                    if (null != $this->_field) {
                        if ($this->_field === $server->field) {
                            $cache->addServer($server->host, $server->port, $server->weight);
                        }
                    } else {
                        $cache->addServer($server->host, $server->port, $server->weight);
                    }
                }
                else
                    $cache->addServer($server->host, $server->port, $server->persistent, $server->weight, $server->timeout, $server->retryInterval, $server->status);
            }
        }
        else
            throw new MemcacheConfigNotArrayException(array('config' => ''));
        //$cache->addServer('localhost', 11211);
    }

    /**
     * Get instance of the memcache or memcached.
     *
     * @return Memcache|Memcached
     */
    public function getMemCache()
    {
        if ($this->_cache !== null)
            return $this->_cache;
        else {
            return $this->_cache = $this->useMemcached ? $this->defaultMemcached() : $this->defaultMemcache();
        }
    }

    /**
     * Get instance of the memcached.
     *
     * @return Memcache|Memcached
     */
    protected function defaultMemcached()
    {
        if (null != $this->_field) {
            static $memcached_instances = array();

            if (array_key_exists($this->_field, $memcached_instances)) {
                $instance = $memcached_instances[$this->_field];
            } else {
                $instance = new \Memcached($this->_field);
                $instance->setOption(\Memcached::OPT_PREFIX_KEY, $this->_field);
                $instance->setOption(\Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
                $memcached_instances[$this->_field] = $instance;
            }
            return $instance;
        } else {
            return new \Memcached;
        }
    }

    /**
     * Get instance of the memcache .
     *
     * @return Memcache|Memcached
     */
    protected function defaultMemcache()
    {
        return new \Memcache;
    }

    /**
     * Get memcache server configurations .
     *
     * @return array
     */
    public function getServers()
    {
        return $this->_servers;
    }

    /**
     * Set memcache server configurations .
     *
     * @param array $config memcache server configurations value.
     * @return void
     */
    public function setServers($config)
    {
        if ($config['useMemcached'] === true) {
            $memcacheConfig = $config['memcached'];
        } else {
            $memcacheConfig = $config['memcache'];
        }
        foreach ($memcacheConfig as $c) {
            $this->_servers[] = new MemCacheServerConfiguration($c);
        }
    }

    /**
     * Retrieves a value from cache with a specified key.
     *
     * @param  string $key a unique key identifying the cached value.
     * @return string
     */
    protected function getValue($key)
    {
        return $this->_cache->get($key);
    }

    /**
     * Retrieves multiple values from cache with the specified keys.
     *
     * @param  array $keys a list of keys identifying the cached values.
     * @return array
     */
    protected function getValues($keys)
    {
        return $this->useMemcached ? $this->_cache->getMulti($keys) : $this->_cache->get($keys);
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
    protected function setValue($key, $value, $expire = 0)
    {

        if ($expire > 0) {
            if ($expire > 2592000) {
                $expire = 2592000 - 1;
            }
        } else if (null !== $this->_expireTime) {
            $expire = $this->_expireTime;
        } else {
            $expire = 0;
        }
        return $this->useMemcached ? $this->_cache->set($key, $value, $expire) : $this->_cache->set($key, $value, 0, $expire);
    }

    /**
     * Stores a value identified by a key into cache if the cache does not contain this key.
     *
     * @param  string  $key    the key identifying the value to be cached
     * @param  string  $value  the value to be cached
     * @param  integer $expire the number of seconds in which the cached value
     *                         will expire. 0 means never expire.
     * @return boolean         true if the value is successfully stored into
     *                         cache, false otherwise
     */
    protected function addValue($key, $value, $expire)
    {
        if ($expire > 0)
            $expire+=time();
        else
            $expire = 0;

        return $this->useMemcached ? $this->_cache->add($key, $value, $expire) : $this->_cache->add($key, $value, 0, $expire);
    }

    /**
     * Deletes a value with the specified key from cache.
     *
     * @param  string $key the key of the value to be deleted.
     * @return boolean     if no error happens during deletion.
     */
    protected function deleteValue($key)
    {
        return $this->_cache->delete($key, 0);
    }

    /**
     * Deletes all values from cache.
     *
     * @return boolean whether the flush operation was successful.
     */
    protected function clearValues()
    {
        return $this->_cache->flush();
    }

    /**
     * Stores a value identified by a key in cache.
     *
     * Keys need to verify
     * Value must a string
     *
     * @param  string  $key    the key identifying the value to be cached.
     * @param  string  $value  the value to be cached.
     * @param  integer $expire the number of seconds in which the cached value
     *                         will expire. 0 means never expire.
     * @return boolean         true if the value is successfully stored into
     *                         cache, false otherwise.
     */
    public function setNginxMemcacheValue($key, $value, $expire = 0)
    {
        if(!is_string($value)){
          throw new MemcacheValueNotStringException(array('value' => $value));
        }
        if(strlen($key)>250){
           throw new  MemcacheKeyTooLongException(array('key' => $key));
        }
        return $this->useMemcached ? $this->_cache->set($key, $value, $expire) : $this->_cache->set($key, $value, 0, $expire);
    }

    /**
     * Retrieves a value from cache with a specified key.
     *
     * @param  string $key a unique key identifying the cached value.
     * @return string
     */
    public function getNginxMemcacheValue($key)
    {
        return $this->_cache->get($key);
    }

}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
