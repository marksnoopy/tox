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
 * @subpackage Tox\Data\Pdo
 * @author     Snakevil Zen <zsnakevil@gmail.com>
 * @copyright  © 2012 szen.in
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Data\Pdo;

use PDOStatement;

use Tox;
use Tox\Data;

class Statement extends Tox\Assembly implements IStatement
{
    protected $columns;

    protected $id;

    protected $options;

    protected $params;

    protected $pdo;

    protected $queryString;

    protected $statement;

    protected $type;

    protected $values;

    public function bindColumn($column, & $param, $type = NULL, $maxlen = NULL, $driverdata = NULL)
    {
        settype($column, 'string');
        $this->columns[$column] = array(& $param, $type, $maxlen, $driverdata);
        if ($this->realised())
        {
            return $this->statement->bindColumn($column, $param, $type, $maxlen, $driverdata);
        }
        return TRUE;
    }

    public function bindParam($parameter, & $variable, $data_type = Data\IPdo::PARAM_STR, $length = NULL,
        $driver_options = array()
    )
    {
        settype($parameter, 'string');
        $this->params[$parameter] = array(& $variable, $data_type, $length, $driver_options);
        unset($this->values[$parameter]);
        if ($this->realised())
        {
            return $this->statement->bindParam($parameter, $variable, $data_type, $length, $driver_options);
        }
        return TRUE;
    }

    public function bindValue($parameter, $value, $data_type = Data\IPdo::PARAM_STR)
    {
        settype($parameter, 'string');
        $this->values[$parameter] = array($value, $data_type);
        unset($this->params[$parameter]);
        if ($this->realised())
        {
            return $this->statement->bindValue($parameter, $value, $data_type);
        }
        return TRUE;
    }

    public function closeCursor()
    {
        $this->params =
        $this->values = array();
        if ($this->realised())
        {
            return $this->statement->closeCursor();
        }
        return TRUE;
    }

    public function columnCount()
    {
        if ($this->realised())
        {
            return $this->statement->columnCount();
        }
        return 0;
    }

    public function __construct(Data\IPdo $pdo, $type, ISql $queryString, $driver_options = array())
    {
        settype($driver_options, 'array');
        $this->columns =
        $this->params =
        $this->values = array();
        $this->id = md5(microtime());
        $this->options = $driver_options;
        $this->pdo = $pdo;
        $this->type = static::TYPE_QUERY == $type ? $type : static::TYPE_PREPARE;
        $this->queryString = $queryString;
        if (static::TYPE_QUERY == $this->type)
        {
            $this->execute();
        }
    }

    public function debugDumpParams()
    {
        return $this->realise()->debugDumpParams();
    }

    public function execute($input_parameters = array(), $partitions = array())
    {
        if (static::TYPE_QUERY == $this->type)
        {
            return $this->realise()->execute();
        }
        settype($partitions, 'array');
        if (!empty($partitions))
        {
            $this->queryString->identifyPartitions($this->pdo, $partitions);
            if ($this->realised() && strval($this->queryString) != $this->statement->queryString)
            {
                unset($this->statement);
            }
        }
        return $this->realise()->execute($input_parameters);
    }

    public function fetch($fetch_style = NULL, $cursor_orientation = Data\IPdo::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        return $this->realise()->fetch($fetch_style, $cursor_orientation, $cursor_offset);
    }

    public function fetchAll($fetch_style = NULL, $fetch_argument = NULL, $ctor_args = array())
    {
        $a_args = array($fetch_style);
        return $this->realise()->fetchAll($fetch_style);
    }

    public function fetchColumn($column_number = 0)
    {
        return $this->realise()->fetchColumn($column_number);
    }

    public function fetchObject($class_name = 'stdClass', $ctor_args = array())
    {
        return $this->realise()->fetchObject($class_name, $ctor_args);
    }

    public function getAttribute($attribute)
    {
        settype($attribute, 'string');
        if ($this->realised())
        {
            return $this->statement->getAttribute($attribute);
        }
        if (array_key_exists($attribute, $this->options))
        {
            return $this->options[$attribute];
        }
        return $this->pdo->getAttribute($attribute);
    }

    public function getColumnMeta($column)
    {
        return $this->realise()->getColumnMeta($column);
    }

    protected function __getId()
    {
        return $this->id;
    }

    protected function __getQueryString()
    {
        return $this->queryString;
    }

    public function nextRowset()
    {
        if ($this->realised())
        {
            return $this->statement->nextRowset();
        }
        return FALSE;
    }

    protected function realise($values = array())
    {
        settype($values, 'array');
        if (!$this->realised() && static::TYPE_QUERY == $this->type)
        {
            $this->statement = $this->pdo->query($this);
            return $this->statement;
        }
        if ($this->realised())
        {
            return $this->statement;
        }
        $this->statement = $this->pdo->prepare($this);
        foreach ($this->columns as $ii => $jj)
        {
            $this->statement->bindColumn($ii, $this->columns[$ii][0], $this->columns[$ii][1],
                $this->columns[$ii][2], $this->columns[$ii][3]
            );
        }
        foreach ($this->params as $ii => $jj)
        {
            $this->statement->bindParam($ii, $this->params[$ii][0], $this->params[$ii][1], $this->params[$ii][2],
                $this->params[$ii][3]
            );
        }
        foreach ($this->values as $ii => $jj)
        {
            $this->statement->bindValue($ii, $jj[0], $jj[1]);
        }
        return $this->statement;
    }

    protected function realised()
    {
        return $this->statement instanceof PDOStatement;
    }

    public function rowCount()
    {
        return $this->realise()->rowCount();
    }

    public function setAttribute($attribute, $value)
    {
        settype($attribute, 'string');
        $this->options[$attribute] = $value;
        if ($this->realised())
        {
            return $this->statement->setAttribute($attribute, $value);
        }
        return TRUE;
    }

    public function setFetchMode($mode)
    {
        settype($mode, 'string');
        $this->options[Data\Pdo::ATTR_DEFAULT_FETCH_MODE] = $mode;
        if ($this->realised())
        {
            return $this->statement->setFetchMode($mode);
        }
        return TRUE;
    }

    public function __toString()
    {
        return strval($this->queryString);
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
