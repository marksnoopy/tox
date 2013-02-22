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
 * @copyright  © 2012 szen.in
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Application\Type;

use Tox\Type;

class SetFilterType extends Type\Enumeration
{
    const BETWEEN = '><';

    const EQUAL = '=';

    const GREATER = '>';

    const IN = 'in';

    const LESS = '<';

    const LIKE = '%';

    const NOT_BETWEEN = '< >';

    const NOT_EQUAL = '!=';

    const NOT_GREATER = '<=';

    const NOT_IN = '!in';

    const NOT_LESS = '>=';

    const NOT_LIKE = '!%';

    protected static $availableValues = array('><', '> <', 'bt', 'between', 'BETWEEN',
        '=', 'eq', 'equal', 'EQUAL',
        '>', 'gt', 'greater', 'GREATER',
        'in', 'IN',
        '<', 'lt', 'less', 'LESS',
        '%', 'lk', 'like', 'LIKE',
        '< >', '!><', '!> <', 'nb', 'nbt', 'not between', 'NOT BETWEEN',
        '!=', '<>', 'ne', 'neq', 'not equal', 'NOT EQUAL',
        '<=', '!>', 'le', 'ng', 'ngt', 'not greater', 'NOT GREATER', 'less equal', 'LESS EQUAL',
        '!in', 'ni', 'nin', 'not in', 'NOT IN',
        '>=', '!<', 'ge', 'nl', 'nlt', 'not less', 'NOT LESS', 'greater equal', 'GREATER EQUAL',
        '!%', '!lk', 'nlk', 'not like', 'NOT LIKE'
    );
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
