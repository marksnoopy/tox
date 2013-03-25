<?php
/**
 * Defines an exception for config the key-value paired data source manually.
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

namespace Tox\Data\KV;

use Tox\Core;

/**
 * Be raised on setting the config when the config  is not  array.
 *
 * @package tox.data.kv
 * @author  Qiang Fu <fuqiang007enter@gmail.com>
 */
final class MemcacheConfigNotArrayException extends Core\Exception
{
    /**
     * {@inheritdoc}
     *
     * > Defined as `0x80020001`.
     */
    const CODE = 0x80040202;

    /**
     * {@inheritdoc}
     *
     * > Defined as `Empty Data Source Exception \'%source$s\'`.
     */
    const MESSAGE = 'Empty Memcache Server Config Data Source Exception \'%source$s\'.';

}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120