<?php
/**
 * Defines the essential behaviors of views.
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

namespace Tox\Application\View;

use Tox\Core;

/**
 * Represents as the abstract view to provide essential behaviors.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * __*ALIAS*__ as `Tox\Application\View`.
 *
 * @package tox.application.view
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class View extends Core\Assembly implements IView
{
    /**
     * Stores the metas.
     *
     * @var mixed[]
     */
    protected $metas;

    /**
     * CONSTRUCT FUNCTION
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param array $metas OPTIONAL. Initial set of metas.
     */
    final public function __construct($metas = array())
    {
        if (!is_array($metas)) {
            $metas = array($metas);
        }
        $this->metas = $metas;
    }

    /**
     * Be invoked on checking the existance of a key.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  string $key Meta key name.
     * @return bool
     */
    final public function offsetExists($key)
    {
        return array_key_exists((string) $key, $this->metas);
    }

    /**
     * Be invoked on retrieving the value of a key.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  string $key Meta key name.
     * @return mixed
     */
    final public function offsetGet($key)
    {
        if ($this->offsetExists($key)) {
            return $this->metas[(string) $key];
        }
        return null;
    }

    /**
     * Be invoked on setting or changing the value of a key.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  string $key   Meta key name.
     * @param  mixed  $value The new value.
     * @return void
     */
    final public function offsetSet($key, $value)
    {
        $this->metas[(string) $key] = $value;
    }

    /**
     * Be invoked on dropping a key.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  string $key Meta key name.
     * @return void
     */
    final public function offsetUnset($key)
    {
        unset($this->metas[(string) $key]);
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  array  $metas A set of metas.
     * @return View
     */
    final public function import($metas)
    {
        if (!is_array($metas)) {
            $metas = array($metas);
        }
        $this->metas = array_merge($this->metas, $metas);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return array
     */
    final public function export()
    {
        return $this->metas;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
