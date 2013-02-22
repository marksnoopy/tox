<?php
/**
 * Represents as an improved PDO.
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
 * @subpackage Tox\Data
 * @author     Snakevil Zen <zsnakevil@gmail.com>
 * @copyright  © 2012 szen.in
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Data;

use PDO as PHPPdo;

use Tox;

class Pdo extends Tox\Assembly implements IPdo
{
    protected $dsn;

    protected static $instances;

    protected $options;

    protected $partitions;

    protected $password;

    protected $pdo;

    protected static $seed;

    protected $statementMetas;

    protected $tables;

    protected $transaction;

    protected $username;

    public function beginTransaction()
    {
        if ($this->connect()->beginTransaction())
        {
            $this->transaction = TRUE;
        }
        return TRUE;
    }

    public function commit()
    {
        if (!$this->transaction)
        {
            return FALSE;
        }
        if ($this->connect()->commit())
        {
            $this->transaction = FALSE;
        }
        return TRUE;
    }

    protected function connect()
    {
        if (!$this->connected())
        {
            $this->pdo = new PHPPdo($this->dsn, $this->username, $this->password, $this->options);
            $s_func = 'on' . $this->pdo->getAttribute(PHPPdo::ATTR_DRIVER_NAME) . 'Connected';
            if (!method_exists($this, $s_func))
            {
                throw new Pdo\UnsupportedDriverToListTablesException(array('driver' => substr($s_func, 2, -9)));
            }
            $this->tables = $this->$s_func();
            if (!is_array($this->tables) || empty($this->tables))
            {
                throw new Pdo\EmptyDataSourceException(array('source' => $this->dsn));
            }
        }
        return $this->pdo;
    }

    public function connected()
    {
        return $this->pdo instanceof PHPPdo;
    }

    protected function __construct($dsn, $username, $password, $driver_options)
    {
        settype($dsn, 'string');
        settype($username, 'string');
        settype($password, 'string');
        settype($driver_options, 'array');
        $this->dsn = $dsn;
        $this->options = $driver_options;
        $this->options[static::ATTR_CASE] = static::CASE_NATURAL;
        $this->options[static::ATTR_ERRMODE] = static::ERRMODE_EXCEPTION;
        $this->options[static::ATTR_DEFAULT_FETCH_MODE] = static::FETCH_ASSOC;
        $this->partitions =
        $this->statementMetas = array();
        $this->password = $password;
        $this->transaction = FALSE;
        $this->username = $username;
    }

    public function exec($statement, $partitions = array())
    {
        settype($partitions, 'array');
        $o_sql = $statement instanceof Pdo\Sql ? $statement : new Pdo\Sql($statement);
        return $this->connect()->exec($o_sql->identifyPartitions($this, $partitions));
    }

    public function getAttribute($attribute)
    {
        if (array_key_exists($attribute, $this->options))
        {
            return $this->options[$attribute];
        }
        return $this->connect()->getAttribute($attribute);
    }

    public static function getAvailableDrivers()
    {
        return PHPPdo::getAvailableDrivers();
    }

    protected function __getDsn()
    {
        return $this->dsn;
    }

    public static function getInstance($dsn, $username = '', $password = '', $driver_options = array())
    {
        settype($dsn, 'string');
        settype($username, 'string');
        if (!is_array(static::$instances))
        {
            static::$seed = microtime();
            static::$instances = array();
        }
        $s_id = sha1($dsn . static::$seed . $username);
        if (!array_key_exists($s_id, static::$instances))
        {
            static::$instances[$s_id] = new static($dsn, $username, $password, $driver_options);
        }
        return static::$instances[$s_id];
    }

    protected function __getPartitions()
    {
        return $this->partitions;
    }

    protected function __getTables()
    {
        return $this->tables;
    }

    protected function __getUsername()
    {
        return $this->username;
    }

    public function identifyPartition($table, $id)
    {
        settype($table, 'string');
        if (isset($this->partitions[$table]))
        {
            return call_user_func($this->partitions[$table], $table, $id);
        }
        return $table;
    }

    public function inTransaction()
    {
        if ($this->connected())
        {
            return $this->pdo->inTransaction();
        }
        return FALSE;
    }

    public function lastInsertId($name = NULL)
    {
        if ($this->connected())
        {
            return $this->pdo->lastInsertId($name);
        }
        return '';
    }

    protected function onMySqlConnected()
    {
        $o_stmt = $this->pdo->query('SHOW TABLES');
        $a_tables = $o_stmt->fetchAll(PHPPdo::FETCH_COLUMN, 0);
        $o_stmt->closeCursor();
        $this->pdo->exec('SET NAMES \'utf8\'');
        return $a_tables;
    }

    protected function onSQLiteConnected()
    {
        $o_stmt = $this->pdo->query('SELECT `name` FROM `sqlite_master` WHERE `type` = \'table\' ORDER BY `name`');
        $a_tables = $o_stmt->fetchAll(PHPPdo::FETCH_COLUMN, 0);
        $o_stmt->closeCursor();
        return $a_tables;
    }

    public function partitionTable($table, $method)
    {
        settype($table, 'string');
        if (!is_callable($method))
        {
            throw new Pdo\InvalidPartitionStrategyException(array('strategy' => $method));
        }
        $this->partitions[$table] = $method;
        return $this;
    }

    public function prepare($statement, $partitions = array(), $driver_options = array())
    {
        if ($statement instanceof Pdo\Statement)
        {
            $a_args = $this->statementMetas[$statement->id];
            unset($this->statementMetas[$statement->id]);
            return $this->connect()->prepare($statement, $a_args);
        }
        $o_sql = $statement instanceof Pdo\Sql ? $statement : new Pdo\Sql($statement);
        $o_stmt = new Pdo\Statement($this, Pdo\Statement::TYPE_PREPARE, $o_sql->identifyPartitions($this, $partitions));
        $this->statementMetas[$o_stmt->id] = $driver_options;
        return $o_stmt;
    }

    public function query($statement, $partitions = array())
    {
        if ($statement instanceof Pdo\Statement)
        {
            $a_args = $this->statementMetas[$statement->id];
            unset($this->statementMetas[$statement->id]);
            $a_args[0] = $statement;
            return call_user_func_array(array($this->connect(), 'query'), $a_args);
        }
        $o_sql = $statement instanceof Pdo\Sql ? $statement : new Pdo\Sql($statement);
        $o_stmt = new Pdo\Statement($this, Pdo\Statement::TYPE_QUERY, $o_sql->identifyPartitions($this, $partitions));
        $this->statementMetas[$o_stmt->id] = func_get_args();
        array_splice($this->statementMetas[$o_stmt->id], 1, 1);
        return $o_stmt;
    }

    public function quote($string, $parameter_type = self::PARAM_STR)
    {
        return $this->connect()->quote($string, $parameter_type);
    }

    public function rollBack()
    {
        if (!$this->connected())
        {
            throw new Pdo\NoActiveTransactionException;
        }
        if ($this->connect()->rollBack())
        {
            $this->transaction = FALSE;
        }
        return TRUE;
    }

    public function setAttribute($attribute, $value)
    {
        switch ($attribute)
        {
            case static::ATTR_CASE:
            case static::ATTR_ERRMODE:
            case static::ATTR_DEFAULT_FETCH_MODE:
                return TRUE;
            case static::ATTR_ORACLE_NULLS:
            case static::ATTR_STRINGIFY_FETCHES:
            case static::ATTR_STATEMENT_CLASS:
            case static::ATTR_TIMEOUT:
            case static::ATTR_AUTOCOMMIT:
            case static::ATTR_EMULATE_PREPARES:
            case static::MYSQL_ATTR_USE_BUFFERED_QUERY:
                break;
            default:
                return FALSE;
        }
        $this->options[$attribute] = $value;
        if ($this->connected())
        {
            $this->pdo->setAttribute($attribute, $value);
        }
        return TRUE;
    }

    public function __toString()
    {
        $s_lob = get_class($this) . ':';
        if ($this->username)
        {
            $s_lob .= $this->username . '@';
        }
        return $s_lob . $this->dsn;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
