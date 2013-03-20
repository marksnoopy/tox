<?php
/**
 * Defines cache dao as a shell of default dao, cache data with 'data.kv.memcache' for get.
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

use Tox\Core;
use Tox\Data;
use Tox\Application;

/**
 * Defines cache dao as a shell of default dao, cache data with 'data.kv.memcache' for get.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * @package tox.application.dao.cache
 * @author  Trainxy Ho <trainxy@gmail.com>
 */
abstract class Dao extends Core\Assembly implements Application\IDao
{

    /**
     * Stores the binded domains for all derived data access objects.
     *
     * @internal
     *
     * @var Data\ISource[]
     */
    protected static $domains = array();

    /**
     * Stores the uniq instance in whole process.
     *
     * @var Tox\Application\Dao\Cache\Dao
     */
    protected static $instances;

    /**
     * Stores an instance of default dao.
     *
     * @var Tox\Application\Dao\Dao
     */
    protected $dao;

    /**
     * Stores an instance of  kv data driver.
     *
     * @var Tox\Data\IKV
     */
    protected $cache;

    /**
     * Stores a cache alive time (ms).
     *
     * @var Tox\Data\IKV
     */
    protected $expire;

    /**
     * CONSTRUCT FUNCTION
     *
     * @param  Application\Dao\Dao $dao   Instance of a default dao
     * @param  Tox\Data\IKV        $cache Instance of a kv data driver
     */
    protected function __construct()
    {

    }

    /**
     * Retrieves the most suitable data domain.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * NOTICE: MOST SUITABLE means that the data domain was binded by the type
     * of current invoker, or its most recently parent.
     *
     * @return Data\ISource
     */
    final protected function getDomain()
    {
        $s_class = get_called_class();
        while (FALSE !== $s_class)
        {
            if (isset(self::$domains[$s_class]))
            {
                return self::$domains[$s_class];
            }
            $s_class = get_parent_class($s_class);
        }
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  Data\ISource $domain Data domain to be binded.
     * @return void
     */
    final public static function bindDomain(Data\ISource $kv)
    {
        if (!$kv instanceof Data\IKv) {
            throw new InvalidCachingDataDomainException();
        }
        self::$domains[get_called_class()] = $kv;
    }

    /**
     * Binds a data access source.
     *
     * @param  Application\Dao\Dao $dao Data access source to be bined.
     * @return self
     */
    public function bind(Application\Dao\Dao $dao)
    {
        if ($this->dao) {
            throw new CachingDataSourceBindedException();
        }
        $this->dao = $dao;
        return $this;
    }

    /**
     * Sets cache expirement time value for each dao.
     *
     * @param   string      $expire Time value (ms).
     * @return  void
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     */
    final public static function getInstance()
    {
        $s_class = get_called_class();
        if (!isset(self::$instances[$s_class])) {
            self::$instances[$s_class] = new $s_class();
        }
        return self::$instances[$s_class];
    }


    /**
     * Create physical data with default dao and set cache.
     *
     * @param  array    $fields     Data of an entity.
     * @return void
     */
    public function create($fields)
    {
        $s_id = $this->getDao()->create($fields);

        $s_key = $this->generateKey($s_id);
        self::getDomain()->set($s_key, $fields, $this->expire);
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

        $a_value = self::getDomain()->get($s_key);
        if ($a_value) {
            return $a_value;
        } else {
            $a_value = $this->getDao()->read($id);
            self::getDomain()->set($s_key, $a_value, $this->expire);
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

        $this->getDao()->update($id, $fields);
        self::getDomain()->set($s_key, $fields, $this->expire);
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

        $this->getDao()->delete($id);
        self::getDomain()->delete($s_key);
    }

    /**
     * Amount of the collection of data with assigned conditions, orders, offset and length.
     *
     * This class not surppose the operation, will be transmitted to default dao.
     *
     * @param  array    $where      Conditions of a model set.
     * @param  int      $offset     Position which set cursor begins.
     * @param  int      $length     Length of data get.
     * @return int
     */
    public function countBy($where = array(), $offset = 0, $length = 0)
    {
        $this->getDao()->countBy($where, $offset, $length);
    }

    /**
     * Get a part of the collection of data with assigned conditions, orders, offset and length.
     *
     * This class not surppose the operation, will be transmitted to default dao.
     *
     * @param  array    $where      Conditions of a model set.
     * @param  array    $orderBy    Orders of a model set.
     * @param  int      $offset     Position which set cursor begins.
     * @param  int      $length     Length of data get.
     * @return mixed[]
     */
    public function listBy($where = array(), $orderBy = array(), $offset = 0, $length = 0)
    {
        $this->getDao()->listBy($where, $orderBy, $offset, $length);
    }

    /**
     * Generate uniq cache key for each entity.
     *
     * @param  string   $id         Identity of an entity.
     * @return string
     */
    protected function generateKey($id)
    {
        $s_class = get_class($this->getDao());
        return md5($s_class . '-' . $id);
    }

    /**
     * Get property of dao.
     *
     * @return Application\Dao\Dao
     */
    protected function getDao()
    {
        if (!$this->dao) {
            throw new DataSourceExpectedException();
        }
        return $this->dao;
    }

}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
