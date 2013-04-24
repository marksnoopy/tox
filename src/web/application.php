<?php
/**
 *
 *
 * This class cannot be instantiated.
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
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Web;

use Tox\Application as TApp;

abstract class Application extends TApp\Application
{
    /**
     * Stores the session.
     *
     * @var ISession
     */
    protected $session;

    /**
     * Initializes the appliance runtime.
     *
     * @return Application
     * @throws InvalidConfiguredSessionTypeException
     */
    protected function init()
    {
        if (isset($this->config['session.type'])) {
            if (!is_subclass_of($this->config['session.type'], 'Tox\\Web\\ISession')) {
                throw new InvalidConfiguredSessionTypeException(array('type' => $this->config['session.type']));
            }
            $this->session = new $this->config['session.type'];
        } else {
            $this->session = $this->getDefaultSession();
        }

        return $this;
    }

    /**
     * Retrieves the default input on demand.
     *
     * @return Request
     */
    protected function getDefaultInput()
    {
        return new Request;
    }

    /**
     * Retrieves the default output on demand.
     *
     * @return Response\Response
     */
    protected function getDefaultOutput()
    {
        return new Response\Response;
    }

    /**
     * Retrieves the session.
     *
     * @return ISession
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Be invoked on retrieving the session.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return ISession
     */
    final protected function toxGetSession()
    {
        return $this->getSession();
    }

    /**
     * Retrieves the default session on demand.
     */
    protected function getDefaultSession()
    {
        //TODO
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
