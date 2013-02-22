<?php
/**
 * Represents as a routing result token.
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
 * @package   Tox\Application\Router
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Application\Router;

use ArrayAccess;

use Tox;

class Token extends Tox\Assembly implements ArrayAccess, IToken
{
    protected $binded;

    protected $controller;

    protected $options;

    public function bind(Array $values)
    {
        array_shift($values);
        for ($ii = count($values), $jj = count($this->options); $ii < $jj; $ii++)
        {
            $values[] = '';
        }
        if ($ii > $jj)
        {
            array_splice($values, $jj);
        }
        if (!empty($this->options))
        {
            $this->options = array_combine(array_keys($this->options), $values);
        }
        return $this;
    }

    public function __construct(Array $options)
    {
        $this->binded = false;
        $this->controller = array_shift($options);
        $this->options = array_fill_keys($options, '');
    }

    public function dump()
    {
        return $this->options;
    }

    protected function __getController()
    {
        return $this->controller;
    }

    public function offsetExists($offset)
    {
        settype($offset, 'string');
        return isset($this->options[$offset]);
    }

    public function offsetGet($offset)
    {
        settype($offset, 'string');
        return $this->options[$offset];
    }

    public function offsetSet($offset, $value)
    {
        return;
    }

    public function offsetUnset($offset)
    {
        return;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
