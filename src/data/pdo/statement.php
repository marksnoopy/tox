<?php
/**
 * Defines the statement for extended PHP data objects.
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

use Exception as PHPException;
use PDOStatement;

use Tox\Core;
use Tox\Data;

/**
 * Represents as a statement for an extended PHP data object.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * @package tox.data.pdo
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Statement extends Core\Assembly implements Data\IPdoStatement
{
    /**
     * Represents initial.
     *
     * @var int
     */
    const STATE_PREPARED = 0;

    /**
     * Represents executed.
     *
     * @var int
     */
    const STATE_EXECUTED = 1;

    /**
     * Represents fetched.
     *
     * @var int
     */
    const STATE_FETCHED = 2;

    /**
     * Represents closed.
     *
     * @var int
     */
    const STATE_CLOSED = 3;

    /**
     * Stores the unique identifier of this instance.
     *
     * @var string
     */
    protected $id;

    /**
     * Stores the host data object.
     *
     * @var Data\IPdo
     */
    protected $pdo;

    /**
     * Stores the statement type.
     *
     * @var const
     */
    protected $type;

    /**
     * Stores the real PHP statement object.
     *
     * @var PDOStatement
     */
    protected $stmt;

    /**
     * Stores the values to be binded.
     *
     * @var array[]
     */
    protected $values;

    /**
     * Stores the raw SQL statement.
     *
     * @var string
     */
    protected $sql;

    /**
     * Stores the status of the instance.
     *
     * @var const
     */
    protected $status;

    /**
     * Stores the attributes to be set.
     *
     * @var mixed[]
     */
    protected $options;

    /**
     * Stores the fetch mode.
     *
     * @var array
     */
    protected $fetchMode;

    /**
     * Stores the result rows set.
     *
     * @var array[]
     */
    protected $rowset;

    /**
     * Stores the amount of result set.
     *
     * @var int
     */
    protected $length;

    /**
     * Stores the cursor of iteration.
     *
     * @var int
     */
    protected $cursor;

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Be invoked on retrieving the unique identifier.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return string
     */
    final protected function toxGetId()
    {
        return $this->getId();
    }

    /**
     * {@inheritdoc}
     *
     * @return const
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Be invoked on retrieving the type.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return const
     */
    final protected function toxGetType()
    {
        return $this->getType();
    }

    /**
     * {@inheritdoc}
     *
     * @return Data\IPdo
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Be invoked on retrieving the host data object.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return Data\IPdo
     */
    final protected function toxGetPdo()
    {
        return $this->getPdo();
    }

    /**
     * Calls the real PHP statement object if realized.
     *
     * @param  string  $method   Method name.
     * @param  mixed   $defaults Defaults value which would be return if not
     *                           realized.
     * @param  mixed[] $params   OPTIONAL. Parameters to the method. An empty
     *                           array defaults.
     * @return mixed
     */
    protected function callOnDemand($method, $defaults, $params = array())
    {
        if (!$this->isRealized()) {
            return $defaults;
        }
        return call_user_func_array(array($this->realize(), $method), $params);
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed $column     Number of the column (1-indexed) or name of the
     *                           column in the result set. If using the column
     *                           name, be aware that the name should match the
     *                           case of the column, as returned by the driver.
     * @param  mixed $param      Name of the PHP variable to which the column
     *                           will be bound.
     * @param  const $type       OPTIONAL. Data type of the parameter, specified
     *                           by the PDO::PARAM_* constants.
     * @param  int   $maxLen     OPTIONAL. A hint for pre-allocation.
     * @param  mixed $driverData OPTIONAL. Optional parameter(s) for the driver.
     * @return self
     *
     * @throws ColumnBindingFailureException If binding failed.
     */
    public function bindColumn($column, & $param, $type = null, $maxLen = null, $driverData = null)
    {
        $column = (string) $column;
        try {
            $this->realize()->bindColumn($column, $param, $type, $maxLen, $driverData);
        } catch (PHPException $ex) {
            throw new ColumnBindingFailureException(array('column' => $column));
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed $parameter     Parameter identifier. For a prepared
     *                              statement using named placeholders, this
     *                              will be a parameter name of the form
     *                              *:name*. For a prepared statement using
     *                              question mark placeholders, this will be the
     *                              1-indexed position of the parameter.
     * @param  mixed $variable      Name of the PHP variable to bind to the SQL
     *                              statement parameter.
     * @param  const $dataType      OPTIONAL. Explicit data type for the
     *                              parameter using the `Tox\Data\IPdo::PARAM_*`
     *                              constants. To return an INOUT parameter from
     *                              a stored procedure, use the bitwise OR
     *                              operator to set the
     *                              `Tox\Data\IPdo::PARAM_INPUT_OUTPUT` bits for
     *                              the dataType parameter.
     * @param  int   $length        OPTIONAL. Length of the data type. To
     *                              indicate that a parameter is an OUT
     *                              parameter from a stored procedure, you must
     *                              explicitly set the length.
     * @param  array $driverOptions OPTIONAL. Attribute values for the
     *                              parameter.
     * @return self
     *
     * @throws ExecutedStatementException   If already executed.
     * @throws ParamBindingFailureException If binding failed.
     */
    public function bindParam(
        $parameter,
        & $variable,
        $dataType = Data\IPdo::PARAM_STR,
        $length = null,
        $driverOptions = array()
    ) {
        if (self::STATE_PREPARED != $this->status) {
            throw new ExecutedStatementException;
        }
        $parameter = (string) $parameter;
        unset($this->values[$parameter]);
        try {
            $this->realize()->bindParam($parameter, $variable, $dataType, $length, $driverOptions);
        } catch (PHPException $ex) {
            throw new ParamBindingFailureException(array('param' => $parameter));
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed $parameter Parameter identifier. For a prepared statement
     *                          using named placeholders, this will be a
     *                          parameter name of the form :name. For a prepared
     *                          statement using question mark placeholders, this
     *                          will be the 1-indexed position of the parameter.
     * @param  mixed $value     The value to bind to the parameter.
     * @param  const $dataType  OPTIONAL. Explicit data type for the parameter
     *                          using the `Tox\Data\IPdo::PARAM_*` constants.
     * @return self
     *
     * @throws ExecutedStatementException   If already executed.
     * @throws ValueBindingFailureException If binding failed.
     */
    public function bindValue($parameter, $value, $dataType = Data\IPdo::PARAM_STR)
    {
        if (self::STATE_PREPARED != $this->status) {
            throw new ExecutedStatementException;
        }
        $parameter = (string) $parameter;
        $this->values[$parameter] = array($value, $dataType);
        try {
            $this->callOnDemand('bindValue', true, array($parameter, $value, $dataType));
        } catch (PHPException $ex) {
            throw new ValueBindingFailureException(array('param' => $parameter));
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return self
     *
     * @throws CursorClosingFailureException If closing failed.
     */
    public function closeCursor()
    {
        try {
            if (self::STATE_FETCHED == $this->status) {
                $this->realize()->closeCursor();
            }
        } catch (PHPException $ex) {
            throw new CursorClosingFailureException;
        }
        $this->status = self::STATE_CLOSED;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function columnCount()
    {
        return $this->readyToFetch()->columnCount();
    }

    /**
     * {@inheritdoc}
     *
     * @param Data\IPdo $pdo  Hosting data object.
     * @param string    $sql  Statement SQL.
     */
    public function __construct(Data\IPdo $pdo, $sql)
    {
        $this->options =
        $this->values = array();
        $this->id = sha1(microtime());
        $this->pdo = $pdo;
        $this->sql = $sql;
        $this->status = self::STATE_PREPARED;
    }

    /**
     * Creates a new prepared statement object.
     *
     * @param  Data\IPdo $pdo Hosting data object.
     * @param  string    $sql Raw statement SQL.
     * @return Prepare
     */
    protected static function newPrepare(Data\IPdo $pdo, $sql)
    {
        return new Prepare($pdo, $sql);
    }

    /**
     * Creates a new query statement object.
     *
     * @param  Data\IPdo $pdo Hosting data object.
     * @param  string    $sql Raw statement SQL.
     * @return Query
     */
    protected static function newQuery(Data\IPdo $pdo, $sql)
    {
        return new Query($pdo, $sql);
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  Data\IPdo $pdo  Hosting data object.
     * @param  const     $type Statement type.
     * @param  string    $sql  Raw statement SQL.
     * @return self
     */
    final public static function manufacture(Data\IPdo $pdo, $type, $sql)
    {
        if (self::TYPE_PREPARE == $type) {
            return static::newPrepare($pdo, $sql);
        }
        return static::newQuery($pdo, $sql);
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function debugDumpParams()
    {
        return $this->realize()->debugDumpParams();
    }

    /**
     * {@inheritdoc}
     *
     * @param  array  $inputParams OPTIONAL. An array of values with as many
     *                             elements as there are bound parameters in the
     *                             SQL statement being executed. All values are
     *                             treated as `Tox\Data\IPdo::PARAM_STR`.
     * @return self
     *
     * @throws ClosedStatementException  If already closed.
     * @throws ExecutingFailureException If execution failed.
     */
    public function execute($inputParameters = array())
    {
        if (self::STATE_EXECUTED == $this->status) {
            return $this;
        }
        try {
            $this->realize()->execute($inputParameters);
            $this->status = self::STATE_EXECUTED;
        } catch (ClosedStatementException $ex) {
            throw $ex;
        } catch (PHPException $ex) {
            throw new ExecutingFailureException;
        }
        return $this;
    }

    /**
     * Executes automatically for later fetching progress.
     *
     * @return PDOStatement
     */
    protected function readyToFetch()
    {
        if (self::STATE_FETCHED != $this->status) {
            $this->execute()->status = self::STATE_FETCHED;
        }
        return $this->realize();
    }

    /**
     * {@inheritdoc}
     *
     * @param  const $fetchStyle        OPTIONAL. Controls how the next row will
     *                                  be returned to the caller.
     * @param  const $cursorOrientation OPTIONAL. For a PDOStatement object
     *                                  representing a scrollable cursor, this
     *                                  value determines which row will be
     *                                  returned to the caller.
     * @param  int   $cursorOffset      OPTIONAL. For a PDOStatement object
     *                                  representing a scrollable cursor for
     *                                  which the cursorOrientation parameter is
     *                                  set to `Tox\Data\IPdo::FETCH_ORI_ABS`,
     *                                  this value specifies the absolute number
     *                                  of the row in the result set that shall
     *                                  be fetched. For a statement object
     *                                  representing a scrollable cursor for
     *                                  which the cursorOrientation parameter is
     *                                  set to `Tox\Data\IPdo::FETCH_ORI_REL`,
     *                                  this value specifies the row to fetch
     *                                  relative to the cursor position before
     *                                  `Tox\Data\IPdoStatement::fetch()` was
     *                                  called.
     * @return mixed
     */
    public function fetch($fetchStyle = null, $cursorOrientation = Data\IPdo::FETCH_ORI_NEXT, $cursorOffset = 0)
    {
        return $this->readyToFetch()->fetch($fetchStyle, $cursorOrientation, $cursorOffset);
    }

    /**
     * {@inheritdoc}
     *
     * @param  const $fetchStyle OPTIONAL. Controls the contents of the returned
     *                           array as documented in
     *                           `Tox\Data\IPdoStatement::fetch()`. Defaults to
     *                           value of
     *                           `Tox\Data\IPdo::ATTR_DEFAULT_FETCH_MODE` (which
     *                           defaults to `Tox\Data\IPdo::FETCH_BOTH`).
     * @param  mixed $fetchArg   OPTIONAL. This argument have a different
     *                           meaning depending on the value of the
     *                           *fetchStyle* parameter.
     * @param  array $ctorArgs   OPTIONAL. Arguments of custom class constructor
     *                           when the *fetchStyle* parameter is
     *                           `Tox\Data\IPdo::FETCH_CLASS`.
     * @return array
     */
    public function fetchAll($fetchStyle = null, $fetchArgument = null, $ctorArgs = array())
    {
        return $this->readyToFetch()->fetchAll($fetchStyle, $fetchArgument, $ctorArgs);
    }

    /**
     * {@inheritdoc}
     *
     * @param  int    $columnNumber OPTIONAL. 0-indexed number of the column you
     *                              wish to retrieve from the row.
     * @return string
     */
    public function fetchColumn($columnNumber = 0)
    {
        return $this->readyToFetch()->fetchColumn($columnNumber);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $className OPTIONAL. Name of the created class.
     * @param  array  $ctorArgs  OPTIONAL. Elements of this array are passed to
     *                           the constructor.
     * @return mixed
     */
    public function fetchObject($className = 'stdClass', $ctorArgs = array())
    {
        return $this->readyToFetch()->fetchObject($className, $ctorArgs);
    }

    /**
     * {@inheritdoc}
     *
     * @param  const $attribute Attribute name.
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        $attribute = (int) $attribute;
        if (isset($this->options[$attribute])) {
            return $this->options[$attribute];
        }
        return $this->realize()->getAttribute($attribute);
    }

    /**
     * {@inheritdoc}
     *
     * @param  int   $column The 0-indexed column in the result set.
     * @return array
     */
    public function getColumnMeta($column)
    {
        return $this->readyToFetch()->getColumnMeta($column);
    }

    /**
     * {@inheritdoc}
     *
     * @return self
     *
     * @throws RowsetIteratingFailureException If rows set iteration failed.
     */
    public function nextRowset()
    {
        try {
            $this->readyToFetch()->nextRowset();
        } catch (PHPException $ex) {
            throw new RowsetIteratingFailureException;
        }
        return $this;
    }

    /**
     * Realizes the PHP statement object.
     *
     * @return PDOStatement
     *
     * @throws ClosedStatementException If already closed.
     */
    protected function realize()
    {
        if (self::STATE_CLOSED == $this->status) {
            throw new ClosedStatementException;
        }
        if ($this->isRealized()) {
            return $this->stmt;
        }
        $this->stmt = $this->pdo->realize($this);
        foreach ($this->values as $ii => $jj) {
            $this->stmt->bindValue($ii, $jj[0], $jj[1]);
        }
        foreach ($this->options as $ii => $jj) {
            $this->stmt->setAttribute($ii, $jj);
        }
        if (null !== $this->fetchMode) {
            call_user_func_array(array($this->stmt, 'setFetchMode'), $this->fetchMode);
        }
        return $this->stmt;
    }

    /**
     * Checks whether the PHP statement object has realized.
     *
     * @return bool
     */
    protected function isRealized()
    {
        return $this->stmt instanceof PDOStatement || $this->stmt instanceof Data\IPdoStatement;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function rowCount()
    {
        return $this->readyToFetch()->rowCount();
    }

    /**
     * {@inheritdoc}
     *
     * @param  const $attribute Attribute name.
     * @param  mixed $value     New value.
     * @return self
     *
     * @throws AttributeSettingFailureException If attributes setting failed.
     */
    public function setAttribute($attribute, $value)
    {
        $attribute = (int) $attribute;
        if (!$this->isRealized()) {
            $this->options[$attribute] = $value;
        } else {
            try {
                $this->realize()->setAttribute($attribute, $value);
            } catch (PHPException $ex) {
                throw new AttributeSettingFailureException(array('attribute' => $attribute));
            }
        };
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param  const $mode The fetch mode must be one of the
     *                     `Tox\Data\IPdo::FETCH_*` constants.
     * @return self
     *
     * @throws FetchModeSettingFailureException If fetch mode setting failed.
     */
    public function setFetchMode($mode)
    {
        $this->fetchMode = func_get_args();
        $this->fetchMode[0] = (int) $mode;
        try {
            $this->callOnDemand('setFetchMode', true, $this->fetchMode);
        } catch (PHPException $ex) {
            throw new FetchModeSettingFailureException;
        }
        return $this;
    }

    /**
     * Be invoked on string casting.
     *
     * @return string
     */
    public function __toString()
    {
        return strval($this->sql);
    }

    /**
     * Counts the rows of the resulting set.
     *
     * @return int
     */
    public function count()
    {
        if (!is_array($this->rowset)) {
            $this->rowset = $this->fetchAll();
            $this->length = count($this->rowset);
        }
        return $this->length;
    }

    /**
     * Resets for iteration.
     *
     * @return void
     */
    public function rewind()
    {
        $this->count();
        $this->cursor = 0;
    }

    /**
     * Retrieves the index of the row point to.
     *
     * @return int
     */
    public function key()
    {
        return $this->valid() ? $this->cursor : false;
    }

    /**
     * Retrieves the fields data of the row point to.
     *
     * @return array
     */
    public function current()
    {
        return $this->valid() ? $this->rowset[$this->cursor] : false;
    }

    /**
     * Points to the next row.
     *
     * @return void
     */
    public function next()
    {
        $this->cursor++;
    }

    /**
     * Checks whether the pointer is to any row or out of bound.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->count() > $this->cursor;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
