<?php
/**
 * Defines the essential behaviors of model entities.
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
 * @copyright © 2012-2013 PHP-Tox.org
 * @license   GNU General Public License, version 3
 */

namespace Tox\Application;

/**
 * Announces the essential behaviors of model entities.
 *
 * @package tox.application
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
interface IModel extends ICommittable
{
    /**
     * Prepares a new model entity.
     *
     * The new model entity WOULD be created in SYNC mode.
     *
     * @param  mixed[] $attributes Attributes of the new model entity.
     * @param  IDao    $dao        OPTIONAL. Data access object in use.
     * @return self
     */
    public static function prepare($attributes, IDao $dao = null);

    /**
     * Alias of `load()'.
     *
     * @deprecated Remained for forward compatibility. Would be removed in some
     *             future version.
     *
     * @param  string $id  The unique identifier of the model entity.
     * @param  IDao   $dao OPTIONAL. Data access object in use.
     * @return self
     */
    public static function setUp($id, IDao $dao = null);

    /**
     * Loads an existant model entity.
     *
     * @param  string $id  The unique identifier of the model entity.
     * @param  IDao   $dao OPTIONAL. Data access object in use.
     * @return self
     *
     * @throws NonExistantEntityException If the model entity does not exist.
     */
    public static function load($id, IDao $dao = null);

    /**
     * Retrieves the unique identifier.
     *
     * @return string
     */
    public function getId();

    /**
     * Destroys the model entity.
     *
     * @return self
     */
    public function terminate();

    /**
     * Returns the unique indentifier on string casting.
     *
     * @return string
     */
    public function __toString();

    /**
     * Checks whether the model entity alive.
     *
     * @return bool
     */
    public function isAlive();

    /**
     * Duplicates a prepared model entity.
     *
     * @return void
     */
    public function __clone();

    /**
     * Imports a model entity from a models set.
     *
     * @param  IModelSet $set The container models set.
     * @param  IDao      $dao Data access object to use.
     * @return self
     */
    public static function import(IModelSet $set, IDao $dao);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
