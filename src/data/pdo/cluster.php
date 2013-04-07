<?php
/**
 * Represents as a master-slaves-paired PDO source.
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
 * @package    Tox
 * @subpackage Tox\Data\Pdo
 * @author     Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012-2013 PHP-Tox.org
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Data\Pdo;

use Tox\Core;
use Tox\Data;

class Cluster extends Core\Assembly implements ICluster
{
    protected $inTransaction;

    protected $master;

    protected $slaves;

    protected $weights;

    public function addSlave(Data\IPdo $slave, $weight = self::WEIGHT_AUTO)
    {
        $s_id = strval($slave);
        $this->slaves[$s_id] = $slave;
        if (static::WEIGHT_AUTO != $weight)
        {
            $weight = 10000 * floatval($weight);
            $this->weights[0][$s_id] = $weight;
        }
        if (!empty($this->weights[1]))
        {
            $this->weights[1] = array();
        }
        return $this;
    }

    public function beginTransaction()
    {
        $this->inTransaction = TRUE;
        return $this->master->beginTransaction();
    }

    protected function choosePdo(Sql $statement)
    {
        if ($this->inTransaction || Sql::TYPE_WRITE == $statement->type)
        {
            return $this->master;
        }
        return $this->chooseSlave();
    }

    protected function chooseSlave()
    {
        $this->fixWeights();
        if (empty($this->slaves))
        {
            return $this->master;
        }
        $i_rand = intval(substr(microtime(), 4, 4));
        $f_sum = 0;
        foreach ($this->weights[1] as $s_id => $f_weight)
        {
            $f_sum += $f_weight;
            if ($f_sum >= $i_rand)
            {
                break;
            }
        }
        return $this->slaves[$s_id];
    }

    public function commit()
    {
        $this->inTransaction = FALSE;
        return $this->master->commit();
    }

    public function __construct(Data\IPdo $master)
    {
        $this->inTransaction = FALSE;
        $this->master = $master;
        $this->slaves = array();
        $this->weights = array(array(), array());
    }

    public function exec($statement, $partitions = array())
    {
        $statement = Sql::parse($statement);
        return $this->choosePdo($statement)->exec($statement, $partitions);
    }

    protected function fixWeights()
    {
        if (empty($this->weights[1]) && !empty($this->slaves))
        {
            $f_avg = 0;
            $f_times = 1;
            $f_total = array_sum($this->weights[0]);
            $i_total = count($this->slaves);
            $i_solid = count($this->weights[0]);
            if (10000 < $f_total)
            {
                $f_times = 10000 / $f_total;
            }
            else
            {
                $f_avg = (10000 - $f_total) / ($i_total - $i_solid);
            }
            reset($this->slaves);
            for ($ii = 0; $ii < $i_total; $ii++)
            {
                list($s_id, ) = each($this->slaves);
                $this->weights[1][$s_id] = isset($this->weights[0][$s_id])
                    ? $f_times * $this->weights[0][$s_id]
                    : $f_avg;
            }
        }
        return $this;
    }

    public function getAttribute($attribute)
    {
        return $this->master->getAttribute($attribute);
    }

    public static function getAvailableDrivers()
    {
        return Data\Pdo::getAvailableDrivers();
    }

    protected function __getMaster()
    {
        return $this->master;
    }

    public function identifyPartition($table, $id)
    {
        return $this->master->identifyPartition($table, $id);
    }

    public function inTransaction()
    {
        return $this->inTransaction;
    }

    public function lastInsertId($name = NULL)
    {
        return $this->master->lastInsertId($name);
    }

    public function partitionTable($table, $method)
    {
        return $this->master->partitionTable($table, $method);
    }

    public function prepare($statement, $partitions = array(), $driver_options = array())
    {
        $statement = Sql::parse($statement);
        return $this->choosePdo($statement)->prepare($statement, $partitions, $driver_options);
    }

    public function query($statement, $partitions = array())
    {
        $statement = Sql::parse($statement);
        return $this->choosePdo($statement)->query($statement, $partitions);
    }

    public function quote($string, $parameter_type = self::PARAM_STR)
    {
        return $this->chooseSlave()->quote($string, $parameter_type);
    }

    public function rollBack()
    {
        $this->inTransaction = FALSE;
        return $this->master->rollBack();
    }

    public function setAttribute($attribute, $value)
    {
        reset($this->slaves);
        for ($ii = 0, $jj = count($this->slaves); $ii < $jj; $ii++)
        {
            list($s_id, $o_pdo) = each($this->slaves);
            $o_pdo->setAttribute($attribute, $value);
        }
        return $this->master->setAttribute($attribute, $value);
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
