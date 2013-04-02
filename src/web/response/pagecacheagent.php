<?php
/**
 * Defines the agent for web applications responses to cache pages contents.
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
 * Represents as the agent for web applications responses to cache pages
 * contents.
 *
 * @package tox.web.response
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
class PageCacheAgent extends Core\Assembly implements Web\IPageCacheAgent
{
    /**
     * Stores the related web response.
     *
     * @var Web\IResponse
     */
    public $response;

    /**
     * Stores the page caching object.
     *
     * @var Web\IPageCache
     */
    protected $cache;

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
     * @param  Web\IPageCache $cache The target page cache object.
     * @return self
     */
    public function attach(Web\IPageCache $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     *
     * @return void
     */
    public function preOutput()
    {
    }

    /**
     * {@inheritdoc}
     *
     * Caching content would be put into the object.
     *
     * @return void
     */
    public function postOutput()
    {
        if (!$this->cache instanceof Web\IPageCache) {
            return $this;
        }
        $this->cache->put($this->response->getBuffer());
        return $this;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
