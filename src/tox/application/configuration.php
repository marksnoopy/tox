<?php

/**
 * Represents as the runtime configuration reader for an application.
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

use Tox;

class Configuration extends Tox\Assembly implements IConfiguration {

    protected $data;
    protected $file;

    public function __construct($file = '') {
        $s_dir = dirname(dirname(dirname(dirname(__FILE__))));
        settype($file, 'string');
        $this->file = $file;
        if (is_file($s_dir.$this->file)) {
            require_once $s_dir.$this->file;
            $this->data = $a_array;
        } else {
            $this->data = array();
        }
    }

    public function getconfig(){
        return $this->data;
    }

    public function export($expr, $defaults = NULL) {
        settype($expr, 'string');
        $s_expr = '@^' . str_replace(array('\\*', '\\\\.+'), array('.+', '\\*'), preg_quote($expr, '@')) . '$@';
        $a_ret = array();
        reset($this->data);
        for ($ii = 0, $jj = count($this->data); $ii < $jj; $ii++) {
            list($s_key, $m_value) = each($this->data);
            if (preg_match($s_expr, $s_key)) {
                $a_ret[$s_key] = $m_value;
            }
        }
        switch (count($a_ret)) {
            case 0:
                if (NULL !== $defaults) {
                    return $defaults;
                }
                if (FALSE === strpos($s_expr, '.+')) {
                    return NULL;
                }
                break;
            case 1:
                if (FALSE === strpos($s_expr, '.+')) {
                    return $a_ret[0];
                }
        }
        return $a_ret;
    }

    public function offsetExists($offset) {
        settype($offset, 'string');
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset) {
        settype($offset, 'string');
        if (!$this->offsetExists($offset)) {
            return NULL;
        }
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value) {
        return;
    }

    public function offsetUnset($offset) {
        return;
    }

}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
