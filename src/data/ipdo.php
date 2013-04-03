<?php
/**
 * Defines the extended behaviors of PHP data objects.
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

namespace Tox\Data;

use PDO as PHPPdo;

/**
 * Announces the extended behaviors of PHP data objects.
 *
 * @package tox.data
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
interface IPdo extends ISource, IPartition
{
    /**
     * Represents a boolean data type.
     *
     * @var int
     */
    const PARAM_BOOL = PHPPdo::PARAM_BOOL;

    /**
     * Represents the SQL NULL data type.
     *
     * @var int
     */
    const PARAM_NULL = PHPPdo::PARAM_NULL;

    /**
     * Represents the SQL INTEGER data type.
     *
     * @var int
     */
    const PARAM_INT = PHPPdo::PARAM_INT;

    /**
     * Represents the SQL CHAR, VARCHAR, or other string data type.
     *
     * @var int
     */
    const PARAM_STR = PHPPdo::PARAM_STR;

    /**
     * Represents the SQL large object data type.
     *
     * @var int
     */
    const PARAM_LOB = PHPPdo::PARAM_LOB;

    /**
     * Represents a recordset type. Not currently supported by any drivers.
     *
     * @var int
     */
    const PARAM_STMT = PHPPdo::PARAM_STMT;

    /**
     * Specifies that the parameter is an INOUT parameter for a stored
     * procedure.
     *
     * You must bitwise-OR this value with an explicit `Tox\Data\IPdo::PARAM_*`
     * data type.
     *
     * @var int
     */
    const PARAM_INPUT_OUTPUT = PHPPdo::PARAM_INPUT_OUTPUT;

    /**
     * Specifies that the fetch method shall return each row as an object with
     * variable names that correspond to the column names returned in the result
     * set.
     *
     * `Tox\Data\IPdo::FETCH_LAZY` creates the object variable names as they are
     * accessed. Not valid inside `Tox\Data\IPdoStatement::fetchAll()`.
     *
     * @var int
     */
    const FETCH_LAZY = PHPPdo::FETCH_LAZY;

    /**
     * Specifies that the fetch method shall return each row as an array indexed
     * by column name as returned in the corresponding result set.
     *
     * If the result set contains multiple columns with the same name,
     * `Tox\Data\IPdo::FETCH_ASSOC` returns only a single value per column name.
     *
     * @var int
     */
    const FETCH_ASSOC = PHPPdo::FETCH_ASSOC;

    /**
     * Specifies that the fetch method shall return each row as an array indexed
     * by column name as returned in the corresponding result set.
     *
     * If the result set contains multiple columns with the same name,
     * `Tox\Data\IPdo::FETCH_NAMED` returns an array of values per column name.
     *
     * @var int
     */
    const FETCH_NAMED = PHPPdo::FETCH_NAMED;

    /**
     * Specifies that the fetch method shall return each row as an array indexed
     * by column number as returned in the corresponding result set, starting at
     * column 0.
     *
     * @var int
     */
    const FETCH_NUM = PHPPdo::FETCH_NUM;

    /**
     * Specifies that the fetch method shall return each row as an array indexed
     * by both column name and number as returned in the corresponding result
     * set, starting at column 0.
     *
     * @var int
     */
    const FETCH_BOTH = PHPPdo::FETCH_BOTH;

    /**
     * Specifies that the fetch method shall return each row as an object with
     * property names that correspond to the column names returned in the result
     * set.
     *
     * @var int
     */
    const FETCH_OBJ = PHPPdo::FETCH_OBJ;

    /**
     * Specifies that the fetch method shall return TRUE and assign the values
     * of the columns in the result set to the PHP variables to which they were
     * bound with the `Tox\Data\IPdoStatement::bindParam()` or
     * `Tox\Data\IPdoStatement::bindColumn()` methods.
     *
     * @var int
     */
    const FETCH_BOUND = PHPPdo::FETCH_BOUND;

    /**
     * Specifies that the fetch method shall return only a single requested
     * column from the next row in the result set.
     *
     * @var int
     */
    const FETCH_COLUMN = PHPPdo::FETCH_COLUMN;

    /**
     * Specifies that the fetch method shall return a new instance of the
     * requested class, mapping the columns to named properties in the class.
     *
     * @var int
     */
    const FETCH_CLASS = PHPPdo::FETCH_CLASS;

    /**
     * Specifies that the fetch method shall update an existing instance of the
     * requested class, mapping the columns to named properties in the class.
     *
     * @var int
     */
    const FETCH_INTO = PHPPdo::FETCH_INTO;

    /**
     * Allows completely customize the way data is treated on the fly (only
     * valid inside `Tox\Data\IPdoStatement::fetchAll()`).
     *
     * @var int
     */
    const FETCH_FUNC= PHPPdo::FETCH_FUNC;

    /**
     * Group return by values. Usually combined with
     * `Tox\Data\IPdo::FETCH_COLUMN` or `Tox\Data\IPdo::FETCH_KEY_PAIR`.
     *
     * @var int
     */
    const FETCH_GROUP = PHPPdo::FETCH_GROUP;

    /**
     * Fetch only the unique values.
     *
     * @var int
     */
    const FETCH_UNIQUE = PHPPdo::FETCH_UNIQUE;

    /**
     * Fetch a two-column result into an array where the first column is a key
     * and the second column is the value.
     *
     * @var int
     */
    const FETCH_KEY_PAIR = PHPPdo::FETCH_KEY_PAIR;

    /**
     * Determine the class name from the value of first column.
     *
     * @var int
     */
    const FETCH_CLASSTYPE = PHPPdo::FETCH_CLASSTYPE;

    /**
     * As `Tox\Data\IPdo::FETCH_INTO` but object is provided as a serialized
     * string.
     *
     * Since PHP 5.3.0 the class constructor is never called if this flag is
     * set.
     *
     * @var int
     */
    const FETCH_SERIALIZE = PHPPdo::FETCH_SERIALIZE;

    /**
     * Call the constructor before setting properties.
     *
     * @var int
     */
    const FETCH_PROPS_LATE = PHPPdo::FETCH_PROPS_LATE;

    /**
     * If this value is FALSE, PDO attempts to disable autocommit so that the
     * connection begins a transaction.
     *
     * @var int
     */
    const ATTR_AUTOCOMMIT = PHPPdo::ATTR_AUTOCOMMIT;

    /**
     * Setting the prefetch size allows you to balance speed against memory
     * usage for your application.
     *
     * Not all database/driver combinations support setting of the prefetch
     * size.
     *
     * A larger prefetch size results in increased performance at the cost of
     * higher memory usage.
     *
     * @var int
     */
    const ATTR_PREFETCH = PHPPdo::ATTR_PREFETCH;

    /**
     * Sets the timeout value in seconds for communications with the database.
     *
     * @var int
     */
    const ATTR_TIMEOUT = PHPPdo::ATTR_TIMEOUT;

    /**
     * Sets the mode of errors exposion.
     *
     * @var int
     */
    const ATTR_ERRMODE = PHPPdo::ATTR_ERRMODE;

    /**
     * This is a read only attribute; it will return information about the
     * version of the database server to which PDO is connected.
     *
     * @var int
     */
    const ATTR_SERVER_VERSION = PHPPdo::ATTR_SERVER_VERSION;

    /**
     * This is a read only attribute; it will return information about the
     * version of the client libraries that the PDO driver is using.
     *
     * @var int
     */
    const ATTR_CLIENT_VERSION = PHPPdo::ATTR_CLIENT_VERSION;

    /**
     * This is a read only attribute; it will return some meta information about
     * the database server to which PDO is connected.
     *
     * @var int
     */
    const ATTR_SERVER_INFO = PHPPdo::ATTR_SERVER_INFO;

    /**
     * This is a read only attribute; it will return the connection status.
     *
     * @var int
     */
    const ATTR_CONNECTION_STATUS = PHPPdo::ATTR_CONNECTION_STATUS;

    /**
     * Force column names to a specific case specified by the
     * `Tox\Data\IPdo::CASE_*` constants.
     *
     * @var int
     */
    const ATTR_CASE = PHPPdo::ATTR_CASE;

    /**
     * Get or set the name to use for a cursor.
     *
     * Most useful when using scrollable cursors and positioned updates.
     *
     * @var int
     */
    const ATTR_CURSOR_NAME = PHPPdo::ATTR_CURSOR_NAME;

    /**
     * Selects the cursor type.
     *
     * PDO currently supports either `Tox\Data\IPdo::CURSOR_FWDONLY` and
     * `Tox\Data\IPdo::CURSOR_SCROLL`. Stick with
     * `Tox\Data\IPdo::CURSOR_FWDONLY` unless you know that you need a
     * scrollable cursor.
     *
     * @var int
     */
    const ATTR_CURSOR = PHPPdo::ATTR_CURSOR;

    /**
     * Returns the name of the driver.
     *
     * @var int
     */
    const ATTR_DRIVER_NAME = PHPPdo::ATTR_DRIVER_NAME;

    /**
     * Converts empty strings to SQL NULL values on data fetches.
     *
     * @var int
     */
    const ATTR_ORACLE_NULLS = PHPPdo::ATTR_ORACLE_NULLS;

    /**
     * Request a persistent connection, rather than creating a new connection.
     *
     * @var int
     */
    const ATTR_PERSISTENT = PHPPdo::ATTR_PERSISTENT;

    /**
     * Retrieves custom statement class name.
     *
     * @var int
     */
    const ATTR_STATEMENT_CLASS = PHPPdo::ATTR_STATEMENT_CLASS;

    /**
     * Prepend the containing catalog name to each column name returned in the
     * result set.
     *
     * The catalog name and column name are separated by a decimal (.)
     * character. Support of this attribute is at the driver level; it may not
     * be supported by your driver.
     *
     * @var int
     */
    const ATTR_FETCH_CATALOG_NAMES = PHPPdo::ATTR_FETCH_CATALOG_NAMES;

    /**
     * Prepend the containing table name to each column name returned in the
     * result set.
     *
     * The table name and column name are separated by a decimal (.) character.
     * Support of this attribute is at the driver level; it may not be supported
     * by your driver.
     *
     * @var int
     */
    const ATTR_FETCH_TABLE_NAMES = PHPPdo::ATTR_FETCH_TABLE_NAMES;

    const ATTR_STRINGIFY_FETCHES = PHPPdo::ATTR_STRINGIFY_FETCHES;

    /**
     * Returns the maximize length of columns.
     *
     * @var int
     */
    const ATTR_MAX_COLUMN_LEN = PHPPdo::ATTR_MAX_COLUMN_LEN;

    /**
     * Returns the default fetch mode.
     *
     * @var int
     */
    const ATTR_DEFAULT_FETCH_MODE = PHPPdo::ATTR_DEFAULT_FETCH_MODE;

    const ATTR_EMULATE_PREPARES = PHPPdo::ATTR_EMULATE_PREPARES;

    /**
     * Do not raise an error or exception if an error occurs.
     *
     * The developer is expected to explicitly check for errors. This is the
     * default mode.
     *
     * @var int
     */
    const ERRMODE_SILENT = PHPPdo::ERRMODE_SILENT;

    /**
     * Issue a PHP E_WARNING message if an error occurs.
     *
     * @var int
     */
    const ERRMODE_WARNING = PHPPdo::ERRMODE_WARNING;

    /**
     * Throw an exception if an error occurs.
     *
     * @var int
     */
    const ERRMODE_EXCEPTION = PHPPdo::ERRMODE_EXCEPTION;

    /**
     * Leave column names as returned by the database driver.
     *
     * @var int
     */
    const CASE_NATURAL = PHPPdo::CASE_NATURAL;

    /**
     * Force column names to lower case.
     *
     * @var int
     */
    const CASE_LOWER = PHPPdo::CASE_LOWER;

    /**
     * Force column names to upper case.
     *
     * @var int
     */
    const CASE_UPPER = PHPPdo::CASE_UPPER;

    const NULL_NATURAL = PHPPdo::NULL_NATURAL;

    const NULL_EMPTY_STRING = PHPPdo::NULL_EMPTY_STRING;

    const NULL_TO_STRING = PHPPdo::NULL_TO_STRING;

    /**
     * Fetch the next row in the result set.
     *
     * Valid only for scrollable cursors.
     *
     * @var int
     */
    const FETCH_ORI_NEXT = PHPPdo::FETCH_ORI_NEXT;

    /**
     * Fetch the previous row in the result set.
     *
     * Valid only for scrollable cursors.
     *
     * @var int
     */
    const FETCH_ORI_PRIOR = PHPPdo::FETCH_ORI_PRIOR;

    /**
     * Fetch the first row in the result set.
     *
     * Valid only for scrollable cursors.
     *
     * @var int
     */
    const FETCH_ORI_FIRST = PHPPdo::FETCH_ORI_FIRST;

    /**
     * Fetch the last row in the result set.
     *
     * Valid only for scrollable cursors.
     *
     * @var int
     */
    const FETCH_ORI_LAST = PHPPdo::FETCH_ORI_LAST;

    /**
     * Fetch the requested row by row number from the result set.
     *
     * Valid only for scrollable cursors.
     *
     * @var int
     */
    const FETCH_ORI_ABS = PHPPdo::FETCH_ORI_ABS;

    /**
     * Fetch the requested row by relative position from the current position of
     * the cursor in the result set.
     *
     * Valid only for scrollable cursors.
     *
     * @var int
     */
    const FETCH_ORI_REL = PHPPdo::FETCH_ORI_REL;

    /**
     * Create a Tox\Data\IPdoStatement object with a forward-only cursor.
     *
     * This is the default cursor choice, as it is the fastest and most common
     * data access pattern in PHP.
     *
     * @var int
     */
    const CURSOR_FWDONLY = PHPPdo::CURSOR_FWDONLY;

    /**
     * Create a Tox\Data\IPdoStatement object with a scrollable cursor.
     *
     * Pass the `Tox\Data\IPdo::FETCH_ORI_*` constants to control the rows
     * fetched from the result set.
     *
     * @var int
     */
    const CURSOR_SCROLL = PHPPdo::CURSOR_SCROLL;

    /**
     * Corresponds to SQLSTATE '00000', meaning that the SQL statement was
     * successfully issued with no errors or warnings.
     *
     * This constant is for your convenience when checking
     * `Tox\Data\IPdo::errorCode()` or `Tox\Data\IPdoStatement::errorCode()` to
     * determine if an error occurred. You will usually know if this is the case
     * by examining the return code from the method that raised the error
     * condition anyway.
     *
     * @var int
     */
    const ERR_NONE = PHPPdo::ERR_NONE;

    /**
     * Allocation event.
     *
     * @var int
     */
    const PARAM_EVT_ALLOC = PHPPdo::PARAM_EVT_ALLOC;

    /**
     * Deallocation event.
     *
     * @var int
     */
    const PARAM_EVT_FREE = PHPPdo::PARAM_EVT_FREE;

    /**
     * Event triggered prior to execution of a prepared statement.
     *
     * @var int
     */
    const PARAM_EVT_EXEC_PRE = PHPPdo::PARAM_EVT_EXEC_PRE;

    /**
     * Event triggered subsequent to execution of a prepared statement.
     *
     * @var int
     */
    const PARAM_EVT_EXEC_POST = PHPPdo::PARAM_EVT_EXEC_POST;

    /**
     * Event triggered prior to fetching a result from a resultset.
     *
     * @var int
     */
    const PARAM_EVT_FETCH_PRE = PHPPdo::PARAM_EVT_FETCH_PRE;

    /**
     * Event triggered subsequent to fetching a result from a resultset.
     *
     * @var int
     */
    const PARAM_EVT_FETCH_POST = PHPPdo::PARAM_EVT_FETCH_POST;

    /**
     * Event triggered during bound parameter registration allowing the driver
     * to normalize the parameter name.
     *
     * @var int
     */
    const PARAM_EVT_NORMALIZE = PHPPdo::PARAM_EVT_NORMALIZE;

    /**
     * Initiates a transaction.
     *
     * @return bool
     */
    public function beginTransaction();

    /**
     * Commits a transaction.
     *
     * @return bool
     */
    public function commit();

    /**
     * Execute an SQL statement and return the number of affected rows.
     *
     * @param  string $statement  SQL statement.
     * @return int
     */
    public function exec($statement);

    /**
     * Retrieve a database connection attribute.
     *
     * @param  const $attribute Attribute name.
     * @return mixed
     */
    public function getAttribute($attribute);

    /**
     * Return an array of available PDO drivers.
     *
     * @return string[]
     */
    public static function getAvailableDrivers();

    /**
     * Checks if inside a transaction.
     *
     * @return bool
     */
    public function inTransaction();

    /**
     * Returns the ID of the last inserted row or sequence value.
     *
     * @param  string $name OPTIONAL. Name of the sequence object from which the
     *                      ID should be returned.
     * @return string
     */
    public function lastInsertId($name = null);

    /**
     * Prepares a statement for execution and returns a statement object.
     *
     * @param  string        $statement     SQL statement.
     * @param  array         $driverOptions OPTIONAL. Attribute values for the
     *                                      `Tox\Data\IPdoStatement` object. An
     *                                      empty array defaults.
     * @return IPdoStatement
     */
    public function prepare($statement, $driverOptions = array());

    /**
     * Executes an SQL statement, returning a result set as a statement object.
     *
     * @param  string        $statement SQL statement.
     * @return IPdoStatement
     */
    public function query($statement);

    /**
     * Quotes a string for use in a query.
     *
     * @param  string $string    The string to be quoted.
     * @param  const  $paramType OPTIONAL. Provides a data type hint for drivers
     *                           that have alternate quoting styles.
     * @return string
     */
    public function quote($string, $paramType = self::PARAM_STR);

    /**
     * Rolls back a transaction.
     *
     * @return bool
     */
    public function rollBack();

    /**
     * Set an attribute.
     * @param  const $attribute Attribute name.
     * @param  mixed $value     New value.
     * @return bool
     */
    public function setAttribute($attribute, $value);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
