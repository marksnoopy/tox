<?php
/**
 * Represents as a collection model of entities.
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
 * @package    Tox\Application
 * @subpackage Model
 * @author     Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012-2013 PHP-Tox.org
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Application\Type;

use Tox\Type\Enumeration;

class SetSortOrder extends Enumeration\Enumeration
{
    const ASCENDING = 'ASC';

    const DESCENDING = 'DESC';

    protected static $availableValues = array('a', 'd', 'A', 'D',
        'asc', 'desc', 'ASC', 'DESC',
        'ascending', 'descending', 'ASCENDING', 'DESCENDING'
    );

    protected function format($value)
    {
        settype($value, 'string');
        switch ($value)
        {
            case 'd':
            case 'D':
            case 'desc':
            case 'DESC':
            case 'descending':
            case 'DESCENDING':
                return static::DESCENDING;
        }
        return static::ASCENDING;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
