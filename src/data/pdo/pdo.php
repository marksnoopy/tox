<?php
/**
 * Defines the extended PHP data object.
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

use PDO as PHPPdo;

use Tox\Core;
use Tox\Data;

/**
 * Represents as an extended PHP data object.
 *
 * __*ALIAS*__ as `Tox\Data\Pdo`.
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
class Pdo extends Core\Assembly implements Data\IPdo
{
    /**
     * Stores the data source name.
     *
     * @var string
     */
    protected $dsn;

    /**
     * Stores the instances.
     *
     * @var Pdo[]
     */
    protected static $instances;

    /**
     * Stores the connection options.
     *
     * @var mixed[]
     */
    protected $options;

    /**
     * Stores the user password to communicate the data source.
     *
     * @var string
     */
    protected $password;

    /**
     * Stores the PHP data object.
     *
     * @var PHPPdo
     */
    protected $pdo;

    /**
     * Stores the seed to generate connection ID.
     *
     * @var string
     */
    protected static $seed;

    /**
     * Stores the status of whether in a transaction.
     *
     * @var bool
     */
    protected $inTransaction;

    /**
     * Stores the username to communicate the data source.
     *
     * @var string
     */
    protected $username;

    /**
     * Stores the statements options.
     *
     * @var array[]
     */
    protected $stmtOptions;

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function beginTransaction()
    {
        if ($this->inTransaction) {
            throw new NestedTransactionUnsupportedException;
        }
        $this->inTransaction = true;
        if (!$this->isConnected()) {
            return true;
        }
        return $this->pdo->beginTransaction();
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function commit()
    {
        if (!$this->inTransaction) {
            throw new NoActiveTransactionException;
        }
        $this->inTransaction = false;
        if (!$this->isConnected()) {
            return true;
        }
        return $this->pdo->commit();
    }

    /**
     * Generates a real PHP data object.
     *
     * @return PHPPdo
     */
    protected function newPHPPdo()
    {
        return new PHPPdo($this->dsn, $this->username, $this->password, $this->options);
    }

    /**
     * Connects to the data source.
     *
     * @return PHPPdo
     */
    protected function connect()
    {
        if (!$this->isConnected())
        {
            $this->pdo = $this->newPHPPdo();
            if ($this->inTransaction) {
                $this->pdo->beginTransaction();
            }
        }
        return $this->pdo;
    }

    /**
     * Checks whether connected to the data source.
     *
     * @return bool
     */
    final public function isConnected()
    {
        return $this->pdo instanceof PHPPdo || $this->pdo instanceof Data\IPdo;
    }

    /**
     * CONSTRUCT FUNCTION
     *
     * @param string $dsn           Data source name.
     * @param string $username      User name to communicate the data source.
     * @param string $password      Password to communicate the data source.
     * @param array  $driverOptions Connection options.
     */
    protected function __construct($dsn, $username, $password, $driverOptions)
    {
        $this->dsn = $dsn;
        $this->password = $password;
        $this->username = $username;
        $this->options = $driverOptions;
        $this->options[static::ATTR_CASE] = static::CASE_NATURAL;
        $this->options[static::ATTR_ERRMODE] = static::ERRMODE_EXCEPTION;
        $this->options[static::ATTR_DEFAULT_FETCH_MODE] = static::FETCH_ASSOC;
        $this->inTransaction = false;
        $this->stmtOptions = array();
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $statement SQL statement.
     * @return int
     */
    public function exec($statement)
    {
        return $this->connect()->exec($statement);
    }

    /**
     * {@inheritdoc}
     *
     * @param  const $attribute Attribute name.
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        if (array_key_exists($attribute, $this->options))
        {
            return $this->options[$attribute];
        }
        return $this->connect()->getAttribute($attribute);
    }

    /**
     * {@inheritdoc}
     *
     * @return string[]
     */
    public static function getAvailableDrivers()
    {
        return PHPPdo::getAvailableDrivers();
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getDsn()
    {
        return $this->dsn;
    }

    /**
     * Be invoked on retrieving the data source name.
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
     * {@inheritdoc}
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Be invoked on retrieving the username.
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
     * @param  string $dsn           Data source name.
     * @param  string $username      OPTIONAL. User name to communicate the data
     *                               source.
     * @param  string $password      OPTIONAL. Password to communicate the data
     *                               source.
     * @param  array  $driverOptions OPTIONAL. Connection options.
     * @return self
     */
    public static function getInstance($dsn, $username = '', $password = '', $driverOptions = array())
    {
        if (!is_array(static::$instances))
        {
            static::$seed = microtime();
            static::$instances = array();
        }
        $s_id = sha1($dsn . static::$seed . $username);
        if (!array_key_exists($s_id, static::$instances))
        {
            static::$instances[$s_id] = new static(
                (string) $dsn,
                (string) $username,
                (string) $password,
                (array) $driverOptions
            );
        }
        return static::$instances[$s_id];
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
        if ($this->isConnected())
        {
            return $this->pdo->lastInsertId($name);
        }
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @param  string    $sql           SQL statement.
     * @param  array     $driverOptions OPTIONAL. Attribute values for the
     *                                  `Tox\Data\Pdo\Statement` object. An
     *                                  empty array defaults.
     * @return Statement
     */
    public function prepare($sql, $driverOptions = array())
    {
        $o_stmt = $this->newStatement($sql, Data\IPdoStatement::TYPE_PREPARE);
        $this->stmtOptions[$o_stmt->getId()] = $driverOptions;
        return $o_stmt;
    }

    /**
     * Generates a statement object.
     *
     * @param  string    $sql  SQL statement.
     * @param  const     $type Statement type.
     * @return Statement
     */
    protected function newStatement($sql, $type)
    {
        return new Statement($this, $type, $sql);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string    $statement SQL statement.
     * @return Statement
     */
    public function query($sql)
    {
        return $this->newStatement($sql, Data\IPdoStatement::TYPE_QUERY);
    }

    /**
     * {@inheritdoc}
     *
     * @param  IPdoStatement $stmt Statement.
     * @return \PDOStatement
     */
    public function realize(Data\IPdoStatement $stmt)
    {
        if (Data\IPdoStatement::TYPE_PREPARE == $stmt->getType()) {
            $options = isset($this->stmtOptions[$stmt->getId()]) ?
                $this->stmtOptions[$stmt->getId()] :
                array();
            return $this->connect()->prepare($stmt, $options);
        }
        return $this->connect()->query($stmt);
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
        return $this->connect()->quote($string, $paramType);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function rollBack()
    {
        if (!$this->inTransaction) {
            throw new NoActiveTransactionException;
        }
        $this->inTransaction = false;
        if (!$this->isConnected()) {
            return true;
        }
        return $this->pdo->rollBack();
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
        switch ($attribute)
        {
            case static::ATTR_CASE:
            case static::ATTR_ERRMODE:
            case static::ATTR_DEFAULT_FETCH_MODE:
                return true;
            case static::ATTR_ORACLE_NULLS:
            case static::ATTR_STRINGIFY_FETCHES:
            case static::ATTR_STATEMENT_CLASS:
            case static::ATTR_TIMEOUT:
            case static::ATTR_AUTOCOMMIT:
            case static::ATTR_EMULATE_PREPARES:
            case static::MYSQL_ATTR_USE_BUFFERED_QUERY:
                break;
            default:
                return false;
        }
        $this->options[$attribute] = $value;
        if ($this->isConnected())
        {
            $this->pdo->setAttribute($attribute, $value);
        }
        return true;
    }

    /**
     * Be invoked on string casting.
     *
     * @return string
     */
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

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
