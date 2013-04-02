<?php
/**
 * Represents as an improved PDO statement.
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

namespace Tox\Data\Pdo;

use Tox\Data;

interface IStatement
{
    const TYPE_PREPARE = 'prepare';

    const TYPE_QUERY = 'query';

    public function bindColumn($column, & $param, $type = NULL, $maxlen = NULL, $driverdata = NULL);

    public function bindParam($parameter, & $variable, $data_type = Data\IPdo::PARAM_STR, $length = NULL,
        $driver_options = array()
    );

    public function bindValue($parameter, $value, $data_type = Data\IPdo::PARAM_STR);

    public function closeCursor();

    public function columnCount();

    public function __construct(Data\IPdo $pdo, $type, ISql $queryString, $driver_options = array());

    public function debugDumpParams();

    public function execute($input_parameters = array());

    public function fetch($fetch_style = NULL, $cursor_orientation = Data\IPdo::FETCH_ORI_NEXT, $cursor_offset = 0);

    public function fetchAll($fetch_style = NULL, $fetch_argument = NULL, $ctor_args = array());

    public function fetchColumn($column_number = 0);

    public function fetchObject($class_name = 'stdClass', $ctor_args = array());

    public function getAttribute($attribute);

    public function getColumnMeta($column);

    public function nextRowset();

    public function rowCount();

    public function setAttribute($attribute, $value);

    public function setFetchMode($mode);
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
