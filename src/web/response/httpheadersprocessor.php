<?php
/**
 * Defines the HTTP headers processor for web applications responses.
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

use Tox\Core;
use Tox\Web;
use Tox\Application;

/**
 * Represents as the HTTP headers processor for web applications responses.
 *
 * @package tox.web.response
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
class HTTPHeadersProcessor extends Core\Assembly implements Web\IHTTPHeadersProcessor
{
    /**
     * Stores the related web response.
     *
     * @var Web\IResponse
     */
    protected $response;

    /**
     * CONSTRUCT FUNCTION
     *
     * @param Web\IResponse $response The web response which to be used for.
     */
    public function __construct(Application\IOutput $response)
    {
        if (!$response instanceof Web\IResponse) {
            throw new ResponseRequiredException(array('output' => $response));
        }
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     *
     * All scheduled HTTP headers would be sent.
     *
     * @return void
     */
    public function preOutput()
    {
        $a_headers = $this->response->getHeaders();
        foreach ($a_headers as $s_field => $a_values) {
            for ($ii = 0, $jj = count($a_values); $ii < $jj; $ii++) {
                $this->sendHeader($s_field, $a_values[$ii]);
            }
        }
        if (count($a_headers)) {
            $this->response->setHeaders(array());
        }
        return $this;
    }

    /**
     * Sends a HTTP header to the user agent.
     *
     * @param  string $field Header field name.
     * @param  string $value The corresponding value.
     * @return self
     */
    protected function sendHeader($field, $value)
    {
        header("$field: $value", false);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     *
     * @return void
     */
    public function postOutput()
    {
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
