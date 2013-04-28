<?php
/**
 * Session provides session-level data management and the related configurations.
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
 * @package   Tox\Web
 * @author    Qiang Fu <fuqiang007enter@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Web;

use Tox\Web;

class SessionFactory
{
    public function openSession($sessionConfig)
    {
        $storge = isset($sessionConfig['storge']) ? $sessionConfig['storge'] : '';
        switch ($storge) {
            case 'memcached':
                $session = new MemcachedHttpSession();
                $session->init($sessionConfig);
                return $session;
                break;
            default:
                $session = new HttpSession();
                $session->init(array());
                return $session;
                break;
        }
    }

}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
