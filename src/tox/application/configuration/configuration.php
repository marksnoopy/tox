<?php
/**
 * Defines the abstract controller of applications.
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

namespace Tox\Application\Configuration;

use Tox\Core;
use Tox\Application;

/**
 * Configuration management for applications.
 *
 * __*ALIAS*__ as `Tox\Application\Configuration`.
 *
 * @package tox.application.configuration
 * @author  Trainxy Ho <trainxy@gmail.com>
 * @since   0.1.0-beta1
 */
class Configuration extends Core\Assembly implements Application\IConfiguration {

    /**
     * Stores global configuration items.
     *
     * @var array
     */
    protected $items = array();

    /**
     * Stores imoprted configuration items.
     *
     * @var array
     */
    protected $imported = array();

    /**
     * Stores loaded configuration items.
     *
     * @var array
     */
    protected $loaded = array();

    /**
     * Stores seted configuration items.
     *
     * @var array
     */
    protected $seted = array();

    /**
     * Stores initial configuration items when constructed.
     *
     * @var array
     */
    protected $inited = array();

    /**
     * {@inheritdoc}
     *
     * @param string $path Path of php configuration file.
     */
    public function __construct($path = '')
    {
        $s_file = $this->getPath($path);
        if ($s_file) {
            require_once $s_file;
            if (isset($a_array) && is_array($a_array)) {
                $this->items = $this->inited = $a_array;
                unset($a_array);
            } else {
                throw new InvalidConfigurationFileException($path);
            }
        } else {
            throw new InvalidConfigurationFileException($path);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param string $path Path of php configuration file.
     * @return void
     */
    public function import($path)
    {
        $s_file = $this->getPath($path);
        if ($s_file) {
            require_once $s_file;
            if (isset($a_array) && is_array($a_array)) {
                $this->imported[$path] = $a_array;
                $this->items = $this->items + $a_array;
                unset($a_array);
            } else {
                throw new InvalidConfigurationFileException($path);
            }
        } else {
            throw new InvalidConfigurationFileException($path);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param array $items A has array of config
     * @return void
     */
    public function load(array $items)
    {
        $this->items = array_merge($this->items, $items);
        $this->loaded = array_merge($this->loaded, $items);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $item  Key of configuration
     * @param mixed  $value Value of configuration
     * @return void
     */
    public function set($item, $value)
    {
        settype($item, 'string');
        $a_item = array($item => $value);

        $this->items = array_merge($this->items, $a_item);
        $this->seted = array_merge($this->seted, $a_item);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $expr     Expr rule to export
     * @param mixed  $defaults Default value
     */
    public function export($expr, $defaults = NULL) {
        settype($expr, 'string');
        $s_expr = '@^' . str_replace(array('\\*', '\\\\.+'), array('.+', '\\*'), preg_quote($expr, '@')) . '$@';
        $a_ret = array();
        reset($this->items);
        for ($ii = 0, $jj = count($this->items); $ii < $jj; $ii++) {
            list($s_key, $m_value) = each($this->items);
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

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function dump()
    {
        return array(
            'global'   => $this->items,
            'imported' => $this->imported,
            'loaded'   => $this->loaded,
            'seted'    => $this->seted
        );
    }

    /**
     * Be invoked on exploring a key in an array or not.
     *
     * @param  string $offset Key of configuration item.
     * @return bool
     */
    public function offsetExists($offset) {
        settype($offset, 'string');
        return array_key_exists($offset, $this->items);
    }

    /**
     * Be invoked on getting a value from array by key.
     *
     * @param  string $offset Key of configuration item.
     * @return mixed
     */
    public function offsetGet($offset) {
        settype($offset, 'string');
        if (!$this->offsetExists($offset)) {
            return NULL;
        }
        return $this->items[$offset];
    }

    /**
     * Be invoked on setting a value from array by key.
     *
     * @param  string $offset Key of configuration item.
     * @param  mixed  $value  Value of configuration item.
     * @return void
     */
    public function offsetSet($offset, $value) {
        settype($offset, 'string');
        $this->items[$offset] = $value;
    }

    /**
     * Be invoked on clearing a value from array by key.
     *
     * @param  string $offset Key of configuration item.
     * @return void
     */
    public function offsetUnset($offset)
    {
        settype($offset, 'string');
        unset($this->items[$offset]);
    }

    /**
     * Change relative path to physical path, and verify path available or not.
     *
     * @param  string $path Relative path of configuration file.
     * @return string|false
     */
    public function getPath($path)
    {
        settype($path, 'string');
        $s_file = getcwd() . '/' . $path;
        if (is_file($s_file) && is_readable($s_file)) {
            return $s_file;
        } else {
            return false;
        }
    }

}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
