<?php
/**
 * Defines the response of web applications.
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

namespace Tox\Web\Response;

use Tox\Application\Output;
use Tox\Web;

/**
 * Represents as the response of web applications.
 *
 * __*ALIAS*__ as `Tox\Web\Response`.
 *
 * @package tox.web.response
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
class Response extends Output\Output implements Web\IResponse
{
    /**
     * Stores the scheduled HTTP headers.
     *
     * @var array
     */
    protected $headers;

    /**
     * CONSTRUCT FUNCTION
     */
    public function __construct()
    {
        parent::__construct();
        $this->headers = array();
        $this->tasks[] = new HttpHeadersProcessor($this);
        $this->tasks[] = new PageCacheAgent($this);
    }

    /**
     * {@inheritdoc}
     *
     * @param  string      $field    Header field name.
     * @param  string|int  $value    Header value.
     * @param  boolean     $replaced OPTIONAL. Whether to replace any other
     *                               existant headers with the same field name.
     * @return self
     */
    public function addHeader($field, $value, $replaced = true)
    {
        $field = (string) $field;
        if ($replaced || !array_key_exists($field, $this->headers)) {
            $this->headers[$field] = array();
        }
        $this->headers[$field][] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  IPageCache $cache The page cache object attached to.
     * @return self
     */
    final public function cacheTo(Web\IPageCache $cache)
    {
        $this->tasks[1]->attach($cache);
        return $this;
    }

    /**
     * Be invoked on retrieving scheduled HTTP headers.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return array
     */
    final protected function __getHeaders()
    {
        return $this->headers;
    }

    /**
     * Be invoked on setting scheduled HTTP headers.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @param  Array  $headers New HTTP headers.
     * @return void
     */
    final protected function __setHeaders(Array $headers)
    {
        $this->headers = array();
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->__getHeaders();
    }

    /**
     * {@inheritdoc}
     *
     * @param  array $headers New HTTP headers.
     * @return self
     */
    public function setHeaders(Array $headers)
    {
        if (!$this->outputting) {
            throw new HeadersReadonlyException;

        }
        $this->__setHeaders($headers);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param  IPageCacheAgent $agent The agent to cache the page content.
     * @return self
     */
    public function setPageCacheAgent(Web\IPageCacheAgent $agent)
    {
        $this->tasks[1] = $agent;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param  IHTTPHeadersProcessor $processor The processor to send headers.
     * @return self
     */
    public function setHeadersProcessor(Web\IHTTPHeadersProcessor $processor)
    {
        $this->tasks[0] = $processor;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string  $url         New target URL.
     * @param  boolean $permanently OPTIONAL. Whether to do either 301 or 302
     *                              redirect.
     * @return self
     */
    public function redirect($url, $permanently = false)
    {
        $this->view = null;
        $this->buffer = '';
        $this->headers = array();
        return $this->setStatus($permanently ? 301 : 302)->addHeader('Location', $url)->close();
    }

    /**
     * {@inheritdoc}
     *
     * @param  int $status Custom HTTP status code.
     * @return self
     */
    public function setStatus($status)
    {
        static $statuses = array(
            100 => '100 Continue',
            101 => '101 Switching Protocols',
            200 => '200 OK',
            201 => '201 Created',
            202 => '202 Accepted',
            203 => '203 Non-Authoritative Information',
            204 => '204 No Content',
            205 => '205 Reset Content',
            206 => '206 Partial Content',
            300 => '300 Multiple Choices',
            301 => '301 Moved Permanently',
            302 => '302 Found',
            303 => '303 See Other',
            304 => '304 Not Modified',
            305 => '305 Use Proxy',
            307 => '307 Temporary Redirect',
            400 => '400 Bad Request',
            401 => '401 Unauthorized',
            402 => '402 Payment Required',
            403 => '403 Forbidden',
            404 => '404 Not Found',
            405 => '405 Method Not Allowed',
            406 => '406 Not Acceptable',
            407 => '407 Proxy Authentication Required',
            408 => '408 Request Timeout',
            409 => '409 Conflict',
            410 => '410 Gone',
            411 => '411 Length Required',
            412 => '412 Precondition Failed',
            413 => '413 Request Entity Too Large',
            414 => '414 Request-URI Too Long',
            415 => '415 Unsupported Media Type',
            416 => '416 Requested Range Not Satisfiable',
            417 => '417 Expectation Failed',
            500 => '500 Internal Server Error',
            501 => '501 Not Implemented',
            502 => '502 Bad Gateway',
            503 => '503 Service Unavailable',
            504 => '504 Gateway Timeout',
            505 => '505 HTTP Version Not Supported'
        );
        $status = (int) $status;
        if (!array_key_exists($status, $statuses)) {
            throw new IllegalHTTPStatusCodeException(array('code' => $status));
        }
        return $this->addHeader('Status', $statuses[$status]);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
