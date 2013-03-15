<?php
/**
 * Defines the behaviors of pages caching objects for web applications.
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

namespace Tox\Web;

/**
 * Announces the behaviors of pages caching objects for web applications.
 *
 * @package tox.web
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
interface IPageCache
{
    /**
     * Puts the content in.
     *
     * @param  string $content Caching content.
     * @return self
     */
    public function put($content);

    /**
     * Be invoked on string type casting.
     *
     * @return string
     */
    public function __toString();
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
