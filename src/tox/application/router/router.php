<?php
/**
 * Represents as the router to analyse applications runtime situations.
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
 * @package   Tox\Application
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Application;

use Exception;

use Tox;

class Router extends Tox\Assembly implements IRouter
{
    protected $routes;

    public function analyse(IInput $input)
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
                    $o_token = new Router\Token($a_options);
                    return $o_token->bind($a_matches);
                }
            }
        }
        throw new Router\UnknownApplicationSituationException(array('input' => $input));
    }

    public function __construct($routes = array())
    {
        settype($routes, 'array');
        $this->routes = array();
        $this->import($routes);
    }

    public function import($routes, $prepend = FALSE)
    {
        settype($routes, 'array');
        settype($prepend, 'bool');
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

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
