<?php
/**
 * Represents as the input for a web application.
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
 * @copyright © 2012-2013 PHP-Tox.org
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Web\Request;

use Tox\Core;
use Tox\Application;
use Tox\Web;

class Request extends Core\Assembly implements Web\IRequest
{
    protected $data;

    public function __construct()
    {
        $this->data = array();
        $this->import('cookie', $_COOKIE)
            ->import('env', $_ENV)
            ->import('files', $_FILES, 2)
            ->import('get', $_GET)
            ->import('post', $_POST)
            ->import('server', $_SERVER);
        $_COOKIE = $_ENV = $_FILES = $_GET = $_POST = $_REQUEST = $_SERVER = array();
    }

    public function getCommandLine()
    {
        return rawurldecode($this->data['server.request_uri']);
    }

    protected function import($prefix, $data, $depth = 1)
    {
        settype($prefix, 'string');
        settype($depth, 'int');
        if (0 < $depth) {
            $depth--;
            settype($data, 'array');
            reset($data);
            for ($ii = 0, $jj = count($data); $ii < $jj; $ii++) {
                list($m_key, $m_data) = each($data);
                $this->import($prefix . '.' . strtolower($m_key), $m_data, $depth);
            }
        } elseif (!$depth) {
            $this->data[$prefix] = $data;
        }
        return $this;
    }

    public function offsetExists($offset)
    {
        settype($offset, 'string');
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        settype($offset, 'string');
        if (!$this->offsetExists($offset)) {
            throw new UnknownMetaException(array('field' => $offset));
        }
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        return;
    }

    public function offsetUnset($offset)
    {
        return;
    }

    public function recruit(Application\IToken $token)
    {
        return $this->import('route', $token->export());
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
