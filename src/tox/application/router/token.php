<?php
/**
 * Defines the applications routing tokens.
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

use Tox\Core;
use Tox\Application;

/**
 * Represents as the applications routing tokens.
 *
 * **THIS CLASS CANNOT BE INHERITED.**
 *
 * @package tox.application.router
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
final class Token extends Core\Assembly implements Application\IToken
{
    /**
     * Stores whether values have been assigned.
     *
     * @var bool
     */
    protected $assigned;

    /**
     * Stores the name of target controller.
     *
     * @var string
     */
    protected $controller;

    /**
     * Stores the options and values.
     *
     * @var mixed[]
     */
    protected $options;

	/**
     * {@inheritdoc}
     *
     * @param  string[]  $values Values of options.
     * @return self
     */
    public function assign(Array $values)
    {
        if ($this->assigned) {
            throw new TokenOptionsAlreadyAssignedException;
        }
        $this->assigned = true;
        if (empty($this->options)) {
            return $this;
        }
        array_shift($values);
        for ($ii = count($values), $jj = count($this->options); $ii < $jj; $ii++)
        {
            $values[] = false;
        }
        if ($ii > $jj)
        {
            array_splice($values, $jj);
        }
        $this->options = array_combine(array_keys($this->options), $values);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param Array $options Available options from a routing rule.
     */
    public function __construct(Array $options)
    {
        $this->assigned = false;
        $this->controller = array_shift($options);
        $this->options = array_fill_keys($options, '');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Be invoked on checking the existance of an option.
     *
     * @param  string $option Option name.
     * @return bool
     */
    public function offsetExists($option)
    {
        return isset($this->options[(string) $option]);
    }

    /**
     * Be invoked on retrieving the value of an option.
     *
     * @param  string $option Option name.
     * @return mixed
     */
    public function offsetGet($option)
    {
        return $this->options[(string) $option];
    }

    /**
     * Be invoked on setting or changing the value of an option.
     *
     * @param  string $option Option name.
     * @param  mixed  $value  The new value.
     * @return void
     */
    public function offsetSet($option, $value)
    {
        return;
    }

    /**
     * Be invoked on dropping an option.
     *
     * @param  string $option Option name.
     * @return void
     */
    public function offsetUnset($option)
    {
        return;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function export()
    {
        return $this->options;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
