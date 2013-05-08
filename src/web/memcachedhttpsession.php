<?php
/**
 * Session provides session-level data management and the related configurations.
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
 * @package   Tox\Web
 * @author    Qiang Fu <fuqiang007enter@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Web;

use Tox\Data\KV;

class MemcachedHttpSession extends HttpSession
{
    /**
     * @var ICache the cache component
     */
    private $cache;

    /**
     * Initializes the memcached config.
     *
     * @param array $memecachedconfig memcached config
     */
    public function init($memecachedconfig)
    {
        $memecachedconfig = $memecachedconfig['config'];
        $this->cache = $this->newMemcache();
        $this->cache->setServers($memecachedconfig);
        $this->cache->init();
        parent::init(array());
    }

    public function newMemcache()
    {
        return new KV\Memcache();
    }

    /**
     * Returns a value indicating whether to use custom session storage.
     *
     * @return boolean whether to use custom storage.
     */
    public function useMemcachedStoreSession()
    {
        return true;
    }

    /**
     * Session read handler.
     *
     * Do not call this method directly.
     *
     * @param  string $id session ID
     *
     * @return string the session data
     */
    public function readSession($id)
    {
        $data = $this->cache->get($this->calculateKey($id));
        return $data === false ? '' : $data;
    }

    /**
     * Session write handler.
     *
     * Do not call this method directly.
     *
     * @param  string  $id     session ID
     * @param  string  $data   session data
     * @return boolean whether session write is successful
     */
    public function writeSession($id, $data)
    {
        return $this->cache->set($this->calculateKey($id), $data, $this->getTimeout());
    }

    /**
     * Session destroy handler.
     *
     * Do not call this method directly.
     *
     * @param string $id session ID
     *
     * @return boolean whether session is destroyed successfully
     */
    public function destroySession($id)
    {
        return $this->cache->delete($this->calculateKey($id));
    }

    /**
     * Generates a unique key used for storing session data in cache.
     *
     * @param string $id session variable name
     *
     * @return string a safe cache key associated with the session variable name
     */
    protected function calculateKey($id)
    {
        return 'memcached' . $id;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
