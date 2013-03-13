<?php
/**
 * A shell of default Dao, cache data with 'data.kv.memcache' for get.
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

namespace Tox\Application\Dao\Cache;

use PDO;

use Tox;
use Tox\Data;

/**
 * A shell of default Dao, cache data with 'data.kv.memcache' for get.
 *
 * @package tox.application.dao
 * @author  Trainxy Ho <trainxy@gmail.com>
 */
abstract class Dao extends Tox\Assembly implements Tox\Application\IDao
{

    protected static $domain;

    protected static $instance;

    protected $dao;

    protected $cache;

    protected $expire;

    /**
     * CONSTRUCT FUNCTION
     */
    public function __construct(Tox\Application\Dao $dao, Data\IKv $cache)
    {
        $this->dao = $dao;
        $this->cache = $cache;
    }

    abstract protected static function getDefaultDao();

    /**
     * Set cache expire value for each dao.
     *
     * @param   string      $expire Time value (ms).
     * @return  NULL
     */
    protected function setExpire($expire)
    {
        $this->expire = $expire;
    }

    /**
     * Get singleton instance of self.
     */
    final public static function getInstance()
    {
        if (!static::$instance instanceof static)
        {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * This class not surppose the operation, will be transmit to default dao.
     */
    public static function bindDomain(Data\ISource $domain)
    {
        $this->dao->bindDomain($domain);
    }

    /**
     * Create physical data with default dao and set cache.
     *
     * @param  array    $fields     Data of an entity.
     * @return NULL
     */
    public function create($fields)
    {
        $this->dao->create($fields);

        $s_key = $this->generateKey($fields);
        $this->cache->set($s_key, $fields, $this->expire);
    }

    /**
     * Read data from cache first, then use default dao.
     *
     * @param  string   $id     Identity of an entity.
     * @reutrn array
     */
    public function read($id)
    {
        $s_key = $this->generateKey($id);

        $a_value = $this->cache->get($s_key);
        if ($a_value) {
            return $a_value;
        } else {
            $a_value = $this->dao->read($id);
            $this->cache->set($s_key, $a_value, $this->expire);
            return $a_value;
        }
    }

    /**
     * Both update from cache and default dao.
     *
     * @param  string   $id         Identity of an entity.
     * @param  array    $fields     Data of an entity.
     * @return bool
     */
    public function update($id, $fields)
    {
        $s_key = $this->generateKey($id);

        $this->dao->update($id, $fields);
        $this->cache->set($s_key, $fields, $this->expire);
    }

    /**
     * Both delete from cache and default dao.
     *
     * @param  string   $id         Identity of an entity.
     * @return bool
     */
    public function delete($id)
    {
        $s_key = $this->generateKey($id);

        $this->dao->delete($id);
        $this->cache->delete($s_key);
    }

    /**
     * This class not surppose the operation, will be transmit to default dao.
     */
    public function countBy($where = array(), $offset = 0, $length = 0)
    {
        $this->dao->countBy($where, $offset, $length);
    }

    /**
     * This class not surppose the operation, will be transmit to default dao.
     */
    public function listAndSortBy($where = array(), $orderBy = array(), $offset = 0, $length = 0)
    {
        $this->dao->listAndSortBy($where, $orderBy, $offset, $length);
    }

    /**
     * Generate uniq cache key for each entity.
     *
     * @param  string   $id         Identity of an entity.
     * @return string
     */
    protected function generateKey($id)
    {
        $s_class = get_class($this->dao);
        return md5($s_class . '-' . $id);
    }

}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
