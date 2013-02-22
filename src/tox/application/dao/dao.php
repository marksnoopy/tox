<?php
/**
 * Represents as the data accessor instance of an entity prototype.
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
 * @package   Tox\Application
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Application;

use PDO;

use Tox;
use Tox\Data;

abstract class Dao extends Tox\Assembly implements IDao
{
    const PK = 'id';

    const TABLE = 'table';

    protected static $domain;

    protected static $instance;

    public static function bindDomain(Data\ISource $domain)
    {
        if (static::$domain instanceof Data\ISource)
        {
            throw new Dao\DataDomainRebindingException(array('domain' => $domain));
        }
        static::$domain = $domain;
    }

    final protected function __construct()
    {
    }

    abstract protected function execute($sql, $params = array());

    final protected function getDomain()
    {
        $s_class = get_called_class();
        while (FALSE !== $s_class)
        {
            if ($s_class::$domain instanceof Data\ISource)
            {
                static::$domain = $s_class::$domain;
                return $s_class::$domain;
            }
            $s_class = get_parent_class($s_class);
        }
    }

    final public static function getInstance()
    {
        if (!static::$instance instanceof static)
        {
            static::$instance = new static;
        }
        return static::$instance;
    }

    final protected function validateWhereFields($where, Array $fields)
    {
        if (!is_array($where))
        {
            throw new Dao\IllegalWhereClauseException(array('where' => $where));
        }
        foreach ($fields as $ii)
        {
            if (!isset($where[$ii]))
            {
                if (!is_array($where[$ii]))
                {
                    continue;
                }
                if (empty($where[$ii]) || 2 < count($where[$ii]))
                {
                    throw new Dao\ExpectedConditionFieldMissingException(array('clause' => $where, 'field' => $ii));
                }
            }
            if (!$where[$ii][1] instanceof Type\SetFilterType)
            {
                throw new Dao\IllegalExpectedConditionFieldException(array('clause' => $where, 'field' => $ii));
            }
            switch ($where[$ii][1])
            {
                case Type\SetFilterType::BETWEEN:
                case Type\SetFilterType::NOT_BETWEEN:
                    if (!is_array($where[$ii][0]) || 2 != count($where[$ii][0]))
                    {
                        throw new Dao\IllegalConditionFieldException(array('field' => $ii,
                                'type' => $where[$ii][1],
                                'value' => $where[$ii][0]
                            )
                        );
                    }
                    break;
                case Type\SetFilterType::IN:
                case Type\SetFilterType::NOT_IN:
                    if (!is_array($where[$ii][0]))
                    {
                        throw new Dao\IllegalConditionFieldException(array('field' => $ii,
                                'type' => $where[$ii][1],
                                'value' => $where[$ii][0]
                            )
                        );
                    }
                    break;
            }
        }
        return TRUE;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
