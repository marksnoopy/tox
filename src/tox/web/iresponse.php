<?php
/**
 * Defines the essential behaviors of web applications response.
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

use Tox\Application;

/**
 * Announces the essential behaviors of web applications response.
 *
 * @package tox.web
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
interface IResponse extends Application\IOutput
{
    /**
     * Adds an extra HTTP to be sent.
     *
     * @param  string      $field    Header field name.
     * @param  string|int  $value    Header value.
     * @param  boolean     $replaced OPTIONAL. Whether to replace any other
     *                               existant headers with the same field name.
     * @return self
     */
    public function addHeader($field, $value, $replaced = true);

    /**
     * Specifies a page cache object.
     *
     * @param  IPageCache $cache The page cache object attached to.
     * @return self
     */
    public function cacheTo(IPageCache $cache);

    /**
     * Retrieves all set HTTP headers.
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Overwrites the sent HTTP headers post-outputting.
     *
     * WARNING: Thie method is designed for tasks to *modify* the headers.
     *
     * @param  array $headers New HTTP headers.
     * @return self
     */
    public function setHeaders(Array $headers);

    /**
     * Uses a customize page cache agent.
     *
     * @param  IPageCacheAgent $agent The agent to cache the page content.
     * @return self
     */
    public function setPageCacheAgent(IPageCacheAgent $agent);

    /**
     * Uses a customize HTTP headers processor.
     *
     * @param  IHTTPHeadersProcessor $processor The processor to send headers.
     * @return self
     */
    public function setHeadersProcessor(IHTTPHeadersProcessor $processor);

    /**
     * Redirects to another URL.
     *
     * NOTICE: All HTTP headers and outputting buffer previously added would be
     * dropped.
     *
     * @param  string  $url         New target URL.
     * @param  boolean $permanently OPTIONAL. Whether to do either 301 or 302
     *                              redirect.
     * @return self
     */
    public function redirect($url, $permanently = false);

    /**
     * Sets the HTTP status manually.
     *
     * @param  int $status Custom HTTP status code.
     * @return self
     */
    public function setStatus($status);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
