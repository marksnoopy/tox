<?php
/**
 * Represents as a PDO source.
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

interface IPdo extends ISource, Pdo\IPartition
{
    const PARAM_BOOL = PHPPdo::PARAM_BOOL;

    const PARAM_NULL = PHPPdo::PARAM_NULL;

    const PARAM_INT = PHPPdo::PARAM_INT;

    const PARAM_STR = PHPPdo::PARAM_STR;

    const PARAM_LOB = PHPPdo::PARAM_LOB;

    const PARAM_STMT = PHPPdo::PARAM_STMT;

    const PARAM_INPUT_OUTPUT = PHPPdo::PARAM_INPUT_OUTPUT;

    const FETCH_LAZY = PHPPdo::FETCH_LAZY;

    const FETCH_ASSOC = PHPPdo::FETCH_ASSOC;

    const FETCH_NAMED = PHPPdo::FETCH_NAMED;

    const FETCH_NUM = PHPPdo::FETCH_NUM;

    const FETCH_BOTH = PHPPdo::FETCH_BOTH;

    const FETCH_OBJ = PHPPdo::FETCH_OBJ;

    const FETCH_BOUND = PHPPdo::FETCH_BOUND;

    const FETCH_COLUMN = PHPPdo::FETCH_COLUMN;

    const FETCH_CLASS = PHPPdo::FETCH_CLASS;

    const FETCH_INTO = PHPPdo::FETCH_INTO;

    const FETCH_FUNC= PHPPdo::FETCH_FUNC;

    const FETCH_GROUP = PHPPdo::FETCH_GROUP;

    const FETCH_UNIQUE = PHPPdo::FETCH_UNIQUE;

    const FETCH_KEY_PAIR = PHPPdo::FETCH_KEY_PAIR;

    const FETCH_CLASSTYPE = PHPPdo::FETCH_CLASSTYPE;

    const FETCH_SERIALIZE = PHPPdo::FETCH_SERIALIZE;

    const FETCH_PROPS_LATE = PHPPdo::FETCH_PROPS_LATE;

    const ATTR_AUTOCOMMIT = PHPPdo::ATTR_AUTOCOMMIT;

    const ATTR_PREFETCH = PHPPdo::ATTR_PREFETCH;

    const ATTR_TIMEOUT = PHPPdo::ATTR_TIMEOUT;

    const ATTR_ERRMODE = PHPPdo::ATTR_ERRMODE;

    const ATTR_SERVER_VERSION = PHPPdo::ATTR_SERVER_VERSION;

    const ATTR_CLIENT_VERSION = PHPPdo::ATTR_CLIENT_VERSION;

    const ATTR_SERVER_INFO = PHPPdo::ATTR_SERVER_INFO;

    const ATTR_CONNECTION_STATUS = PHPPdo::ATTR_CONNECTION_STATUS;

    const ATTR_CASE = PHPPdo::ATTR_CASE;

    const ATTR_CURSOR_NAME = PHPPdo::ATTR_CURSOR_NAME;

    const ATTR_CURSOR = PHPPdo::ATTR_CURSOR;

    const ATTR_DRIVER_NAME = PHPPdo::ATTR_DRIVER_NAME;

    const ATTR_ORACLE_NULLS = PHPPdo::ATTR_ORACLE_NULLS;

    const ATTR_PERSISTENT = PHPPdo::ATTR_PERSISTENT;

    const ATTR_STATEMENT_CLASS = PHPPdo::ATTR_STATEMENT_CLASS;

    const ATTR_FETCH_CATALOG_NAMES = PHPPdo::ATTR_FETCH_CATALOG_NAMES;

    const ATTR_FETCH_TABLE_NAMES = PHPPdo::ATTR_FETCH_TABLE_NAMES;

    const ATTR_STRINGIFY_FETCHES = PHPPdo::ATTR_STRINGIFY_FETCHES;

    const ATTR_MAX_COLUMN_LEN = PHPPdo::ATTR_MAX_COLUMN_LEN;

    const ATTR_DEFAULT_FETCH_MODE = PHPPdo::ATTR_DEFAULT_FETCH_MODE;

    const ATTR_EMULATE_PREPARES = PHPPdo::ATTR_EMULATE_PREPARES;

    const ERRMODE_SILENT = PHPPdo::ERRMODE_SILENT;

    const ERRMODE_WARNING = PHPPdo::ERRMODE_WARNING;

    const ERRMODE_EXCEPTION = PHPPdo::ERRMODE_EXCEPTION;

    const CASE_NATURAL = PHPPdo::CASE_NATURAL;

    const CASE_LOWER = PHPPdo::CASE_LOWER;

    const CASE_UPPER = PHPPdo::CASE_UPPER;

    const NULL_NATURAL = PHPPdo::NULL_NATURAL;

    const NULL_EMPTY_STRING = PHPPdo::NULL_EMPTY_STRING;

    const NULL_TO_STRING = PHPPdo::NULL_TO_STRING;

    const FETCH_ORI_NEXT = PHPPdo::FETCH_ORI_NEXT;

    const FETCH_ORI_PRIOR = PHPPdo::FETCH_ORI_PRIOR;

    const FETCH_ORI_FIRST = PHPPdo::FETCH_ORI_FIRST;

    const FETCH_ORI_LAST = PHPPdo::FETCH_ORI_LAST;

    const FETCH_ORI_ABS = PHPPdo::FETCH_ORI_ABS;

    const FETCH_ORI_REL = PHPPdo::FETCH_ORI_REL;

    const CURSOR_FWDONLY = PHPPdo::CURSOR_FWDONLY;

    const CURSOR_SCROLL = PHPPdo::CURSOR_SCROLL;

    const ERR_NONE = PHPPdo::ERR_NONE;

    const PARAM_EVT_ALLOC = PHPPdo::PARAM_EVT_ALLOC;

    const PARAM_EVT_FREE = PHPPdo::PARAM_EVT_FREE;

    const PARAM_EVT_EXEC_PRE = PHPPdo::PARAM_EVT_EXEC_PRE;

    const PARAM_EVT_EXEC_POST = PHPPdo::PARAM_EVT_EXEC_POST;

    const PARAM_EVT_FETCH_PRE = PHPPdo::PARAM_EVT_FETCH_PRE;

    const PARAM_EVT_FETCH_POST = PHPPdo::PARAM_EVT_FETCH_POST;

    const PARAM_EVT_NORMALIZE = PHPPdo::PARAM_EVT_NORMALIZE;

    public function beginTransaction();

    public function commit();

    public function exec($statement, $partitions = array());

    public function getAttribute($attribute);

    public static function getAvailableDrivers();

    public function inTransaction();

    public function lastInsertId($name = NULL);

    public function prepare($statement, $partitions = array(), $driver_options = array());

    public function query($statement, $partitions = array());

    public function quote($string, $parameter_type = self::PARAM_STR);

    public function rollBack();

    public function setAttribute($attribute, $value);
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:

