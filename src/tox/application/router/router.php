<?php
/**
 * Defines the applications routers.
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

namespace Tox\Application\Router;

use Exception;

use Tox\Core;
use Tox\Application;

/**
 * Represents as the applications routing tokens.
 *
 * __*ALIAS*__ as `Tox\Application\Router`.
 *
 * @package tox.application.router
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class Router extends Core\Assembly implements Application\IRouter
{
    /**
     * Stores the imported routing rules.
     *
     * @var array[]
     */
    protected $routes;

    /**
     * {@inheritdoc}
     *
     * @param  Application\IInput $input Applications input.
     * @return Application\IToken
     */
    public function analyse(Application\IInput $input)
    {
        $s_sense = $input->getCommandLine();
        for ($ii = 0, $jj = count($this->routes); $ii < $jj; $ii++)
        {
            reset($this->routes[$ii]);
            for ($kk = 0, $ll = count($this->routes[$ii]); $kk < $ll; $kk++)
            {
                list($s_pattern, $a_options) = each($this->routes[$ii]);
                if (preg_match($s_pattern, $s_sense, $a_matches))
                {
                    $o_token = new Token($a_options);
                    return $o_token->assign($a_matches);
                }
            }
        }
        throw new UnknownApplicationSituationException(array('input' => $input));
    }

    /**
     * {@inheritdoc}
     *
     * @param array[] $routes OPTIONAL. Initial routing rules.
     */
    public function __construct($routes = array())
    {
        $routes = (array) $routes;
        $this->routes = array();
        $this->import($routes);
    }

    /**
     * {@inheritdoc}
     *
     * @param  array[]  $routes  Routing rules to be imported.
     * @param  boolean  $prepend OPTIONAL. Whether prepending the rules to
     *                           existant. FALSE defaults.
     * @return self
     */
    public function import($routes, $prepend = false)
    {
        $routes = (array) $routes;
        $prepend = (bool) $prepend;
        if (empty($routes))
        {
            return $this;
        }
        $a_routes = array();
        reset($routes);
        for ($ii = 0, $jj = count($routes); $ii < $jj; $ii++)
        {
            list($s_pattern, $a_options) = each($routes);
            $s_pattern = '@^' . str_replace('@', '\\@', $s_pattern) . '$@';
            $a_routes[$s_pattern] = $a_options;
        }
        if ($prepend)
        {
            array_unshift($this->routes, $a_routes);
        }
        else
        {
            $this->routes[] = $a_routes;
        }
        return $this;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
