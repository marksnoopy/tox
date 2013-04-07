<?php
/**
 * Defines the extended behaviors of PHP data statement objects.
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

namespace Tox\Data;

/**
 * Announces the extended behaviors of PHP data statement objects.
 *
 * @package tox.data
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
interface IPdoStatement
{
    /**
     * Represents a statement by `Tox\Data\IPdo::prepare()`.
     *
     * @var string
     */
    const TYPE_PREPARE = 'prepare';

    /**
     * Represents a statement by `Tox\Data\IPdo::query()`.
     *
     * @var string
     */
    const TYPE_QUERY = 'query';

    /**
     * Retrieves the unique identifier.
     *
     * @return string
     */
    public function getId();

    /**
     * Retrieves the type.
     *
     * @return const
     */
    public function getType();

    /**
     * Bind a column to a PHP variable.
     *
     * @param  mixed $column     Number of the column (1-indexed) or name of the
     *                           column in the result set. If using the column
     *                           name, be aware that the name should match the
     *                           case of the column, as returned by the driver.
     * @param  mixed $param      Name of the PHP variable to which the column
     *                           will be bound.
     * @param  const $type       OPTIONAL. Data type of the parameter, specified
     *                           by the PDO::PARAM_* constants.
     * @param  int   $maxlen     OPTIONAL. A hint for pre-allocation.
     * @param  mixed $driverdata OPTIONAL. Optional parameter(s) for the driver.
     * @return bool
     */
    public function bindColumn($column, & $param, $type = null, $maxlen = null, $driverdata = null);

    /**
     * Binds a parameter to the specified variable name.
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
     * @return bool
     */
    public function bindParam(
        $parameter,
        & $variable,
        $dataType = IPdo::PARAM_STR,
        $length = null,
        $driverOptions = array()
    );

    /**
     * Binds a value to a parameter.
     *
     * @param  mixed $parameter Parameter identifier. For a prepared statement
     *                          using named placeholders, this will be a
     *                          parameter name of the form :name. For a prepared
     *                          statement using question mark placeholders, this
     *                          will be the 1-indexed position of the parameter.
     * @param  mixed $value     The value to bind to the parameter.
     * @param  const $dataType  OPTIONAL. Explicit data type for the parameter
     *                          using the `Tox\Data\IPdo::PARAM_*` constants.
     * @return bool
     */
    public function bindValue($parameter, $value, $dataType = IPdo::PARAM_STR);

    /**
     * Closes the cursor, enabling the statement to be executed again.
     *
     * @return bool
     */
    public function closeCursor();

    /**
     * Returns the number of columns in the result set.
     *
     * @return int
     */
    public function columnCount();

    /**
     * CONSTRUCT FUNCTION
     * @param IPdo   $pdo  Hosting data object.
     * @param const  $type Type
     * @param string $sql  Statement SQL.
     */
    public function __construct(IPdo $pdo, $type, $sql);

    /**
     * Dump an SQL prepared command.
     *
     * @return bool
     */
    public function debugDumpParams();

    /**
     * Executes a prepared statement.
     *
     * @param  array  $inputParams OPTIONAL. An array of values with as many
     *                             elements as there are bound parameters in the
     *                             SQL statement being executed. All values are
     *                             treated as `Tox\Data\IPdo::PARAM_STR`.
     * @return bool
     */
    public function execute($inputParams = array());

    /**
     * Fetches the next row from a result set.
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
    public function fetch($fetchStyle = null, $cursorOrientation = IPdo::FETCH_ORI_NEXT, $cursorOffset = 0);

    /**
     * Returns an array containing all of the result set rows.
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
    public function fetchAll($fetchStyle = null, $fetchArg = null, $ctorArgs = array());

    /**
     * Returns a single column from the next row of a result set.
     *
     * @param  int    $columnNumber OPTIONAL. 0-indexed number of the column you
     *                              wish to retrieve from the row.
     * @return string
     */
    public function fetchColumn($columnNumber = 0);

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param  string $className OPTIONAL. Name of the created class.
     * @param  array  $ctorArgs  OPTIONAL. Elements of this array are passed to
     *                           the constructor.
     * @return mixed
     */
    public function fetchObject($className = 'stdClass', $ctorArgs = array());

    /**
     * Retrieve a statement attribute.
     *
     * @param  const $attribute Attribute name.
     * @return mixed
     */
    public function getAttribute($attribute);

    /**
     * Returns metadata for a column in a result set.
     *
     * @param  int   $column The 0-indexed column in the result set.
     * @return array
     */
    public function getColumnMeta($column);

    /**
     * Advances to the next rowset in a multi-rowset statement handle.
     *
     * @return bool
     */
    public function nextRowset();

    /**
     * Returns the number of rows affected by the last SQL statement.
     *
     * @return int
     */
    public function rowCount();

    /**
     * Set a statement attribute.
     *
     * @param  const $attribute Attribute name.
     * @param  mixed $value     New value.
     * @return bool
     */
    public function setAttribute($attribute, $value);

    /**
     * Set the default fetch mode for this statement.
     *
     * @param  const $mode The fetch mode must be one of the
     *                     `Tox\Data\IPdo::FETCH_*` constants.
     * @return bool
     */
    public function setFetchMode($mode);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
