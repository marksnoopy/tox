<?php
/**
 * Defines the behaviors of boxable objects.
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

namespace Tox\Type;

/**
 * Announces the behaviors of boxable objects.
 *
 * @package tox.type
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.2.0
 */
interface IBoxable
{
    /**
     * CONSTRUCT FUNCTION
     *
     * @param mixed $value Internal scalar value.
     */
    public function __construct($value);

    /**
     * Sets the reference ID.
     *
     * NOTICE: This method is designed for internal exchange, DO NOT call it
     * manually.
     *
     * @internal
     *
     * @param  IVarbase $varbase Variables manager.
     * @return string
     */
    public function setRef(IVarbase $varbase);

    /**
     * Retrieves the reference ID.
     *
     * NOTICE: This method is designed for internal exchange, DO NOT call it
     * manually.
     *
     * @internal
     *
     * @return string
     */
    public function getRef();

    /**
     * DESTRUCT FUNCTION
     */
    public function __destruct();

    /**
     * Retrieves the internal scalar value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Be invoked on string casting.
     *
     * @return string
     */
    public function __toString();

    /**
     * Boxes an internal scalar value.
     *
     * @param  mixed $value Original internal scalar value.
     * @return self
     */
    public static function & box($value);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
