<?php
/**
 * Defines the master-slaves in-replication data objects.
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

use Tox\Core;
use Tox\Data;

/**
 * Represents as a master-slaves in-replication data object.
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
class Cluster extends Core\Assembly implements ICluster
{
    /**
     * Represents the precision to calculate weights.
     *
     * @var int
     */
    const PRECISION = 10000;

    /**
     * Stores the unique identifier of the master object.
     *
     * @var string
     */
    protected $id;

    /**
     * Stores the data source name of the master object.
     *
     * @var string
     */
    protected $dsn;

    /**
     * Stores the username of the master object.
     *
     * @var string
     */
    protected $username;

    /**
     * Stores the status of transactions.
     *
     * @var bool
     */
    protected $inTransaction;

    /**
     * Stores the master object.
     *
     * @var Data\IPdo
     */
    protected $master;

    /**
     * Stores the slave objects.
     *
     * @var Data\IPdo[]
     */
    protected $slaves;

    /**
     * Stores the weights of slave objects.
     *
     * @var array[]
     */
    protected $weights;

    /**
     * {@inheritdoc}
     *
     * @param  Data\IPdo $slave  An extra data object which in-replication to
     *                           the master.
     * @param  float     $weight Using weight of the data object.
     * @return self
     */
    public function addSlave(Data\IPdo $slave, $weight = self::WEIGHT_AUTO)
    {
        $this->slaves[$slave->getId()] = $slave;
        $this->weights[1] = array();
        if (self::WEIGHT_AUTO != $weight) {
            $weight = self::PRECISION * floatval($weight);
            $this->weights[0][$slave->getId()] = $weight;
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function beginTransaction()
    {
        $this->inTransaction = true;
        return $this->master->beginTransaction();
    }

    /**
     * Detects to use master or a slave object.
     *
     * @param  string    $sql Raw statement SQL.
     * @return Data\IPdo
     */
    protected function choosePdo($sql)
    {
        return ($this->inTransaction || 0 === preg_match('@^\s*SELECT\s+@i', $sql)) ?
            $this->master :
            $this->chooseSlave();
    }

    /**
     * Detects which slave object to be used.
     *
     * @return Data\IPdo
     */
    protected function chooseSlave()
    {
        if (empty($this->slaves)) {
            return $this->master;
        }
        $i_rand = strlen(strval(self::PRECISION)) - 1;
        $i_rand = intval(substr(microtime(), 2, $i_rand));
        $f_sum = 0;
        foreach ($this->fixWeights()->weights[1] as $ii => $jj) {
            $f_sum += $jj;
            if ($f_sum >= $i_rand) {
                break;
            }
        }
        return $this->slaves[$ii];
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function commit()
    {
        $this->inTransaction = false;
        return $this->master->commit();
    }

    /**
     * {@inheritdoc}
     *
     * @param Data\IPdo $master The main data object.
     */
    public function __construct(Data\IPdo $master)
    {
        $this->inTransaction = false;
        $this->master = $master;
        $this->slaves = array();
        $this->weights = array(array(), array());
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $statement  SQL statement.
     * @return int
     */
    public function exec($sql)
    {
        return $this->choosePdo($sql)->exec($sql);
    }

    /**
     * Fixes the weights of slave objects to be calculated.
     *
     * @return self
     */
    protected function fixWeights()
    {
        if (empty($this->weights[2]) && !empty($this->slaves)) {
            $f_avg = 0;
            $f_times = 1;
            $f_total = array_sum($this->weights[0]);
            $i_total = count($this->slaves);
            $i_solid = count($this->weights[0]);
            if (self::PRECISION < $f_total) {
                $f_times = self::PRECISION / $f_total;
            } else {
                $f_avg = ($i_total == $i_solid) ? 0 : ((self::PRECISION - $f_total) / ($i_total - $i_solid));
            }
            foreach ($this->slaves as $ii => $jj) {
                $this->weights[1][$ii] = isset($this->weights[0][$ii]) ? ($f_times * $this->weights[0][$ii]) : $f_avg;
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param  const $attribute Attribute name.
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        return $this->master->getAttribute($attribute);
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public static function getAvailableDrivers()
    {
        return Pdo::getAvailableDrivers();
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $name OPTIONAL. Name of the sequence object from which the
     *                      ID should be returned.
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->master->lastInsertId($name);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string        $statement     SQL statement.
     * @param  array         $driverOptions OPTIONAL. Attribute values for the
     *                                      `Tox\Data\IPdoStatement` object. An
     *                                      empty array defaults.
     * @return IPdoStatement
     */
    public function prepare($sql, $driverOptions = array())
    {
        return $this->choosePdo($sql)->prepare($sql, $driverOptions);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string        $statement SQL statement.
     * @return IPdoStatement
     */
    public function query($sql)
    {
        return $this->choosePdo($sql)->query($sql);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $string    The string to be quoted.
     * @param  const  $paramType OPTIONAL. Provides a data type hint for drivers
     *                           that have alternate quoting styles.
     * @return string
     */
    public function quote($string, $paramType = self::PARAM_STR)
    {
        return $this->master->quote($string, $paramType);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function rollBack()
    {
        $this->inTransaction = false;
        return $this->master->rollBack();
    }

    /**
     * {@inheritdoc}
     *
     * @param  const $attribute Attribute name.
     * @param  mixed $value     New value.
     * @return bool
     */
    public function setAttribute($attribute, $value)
    {
        foreach ($this->slaves as $ii => $jj) {
            $jj->setAttribute($attribute, $value);
        }
        return $this->master->setAttribute($attribute, $value);
    }

    /**
     * Retrieves the unique identifier of the master object.
     *
     * @return string
     */
    public function getId()
    {
        return $this->master->getId();
    }

    /**
     * Be invoked on retrieving the unique identifier of the master object.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return string
     */
    final protected function toxGetId()
    {
        return $this->getId();
    }

    /**
     * Retrieves the data source name of the master object.
     *
     * @return string
     */
    public function getDsn()
    {
        return $this->master->getDsn();
    }

    /**
     * Be invoked on retrieving the data source name of the master object.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return string
     */
    final protected function toxGetDsn()
    {
        return $this->getDsn();
    }

    /**
     * Retrieves the account name of the master object.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->master->getUsername();
    }

    /**
     * Be invoked on retrieving the username of the master object.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return string
     */
    final protected function toxGetUsername()
    {
        return $this->getUsername();
    }

    /**
     * {@inheritdoc}
     *
     * @param  Data\IPdoStatement $stmt Statement.
     * @return void
     *
     * @throws ClusterOopsException
     */
    public function realize(Data\IPdoStatement $stmt)
    {
        throw new ClusterOopsException(array('feature' => 'realize'));
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  string $dsn           Data source name.
     * @param  string $username      OPTIONAL. User name to communicate the data
     *                               source.
     * @param  string $password      OPTIONAL. Password to communicate the data
     *                               source.
     * @param  array  $driverOptions OPTIONAL. Connection options.
     * @return self
     */
    final public static function getInstance($dsn, $username = '', $password = '', $driverOptions = array())
    {
        return static::newCluster(static::newPdo($dsn, $username, $password, $driverOptions));
    }

    /**
     * Creates a new cluster.
     *
     * @param  Data\IPdo $master Data object to be used as master.
     * @return self
     */
    protected static function newCluster(Data\IPdo $master)
    {
        return new static($master);
    }

    /**
     * Creates a new data object.
     *
     * @param  string $dsn           Data source name.
     * @param  string $username      OPTIONAL. User name to communicate the data
     *                               source.
     * @param  string $password      OPTIONAL. Password to communicate the data
     *                               source.
     * @param  array  $driverOptions OPTIONAL. Connection options.
     * @return Pdo
     */
    protected static function newPdo($dsn, $username = '', $password = '', $driverOptions = array())
    {
        return new Pdo($dsn, $username, $password, $driverOptions);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
