<?php
/**
 * Defines the essential behaviors of models sets.
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

use Countable;
use Iterator;

/**
 * Announces the essential behaviors of models sets.
 *
 * @package tox.application
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
interface IModelSet extends ICommittable, Countable, Iterator
{
    /**
     * Appends a model.
     *
     * @param  IModel $entity The model to be appended.
     * @return self
     */
    public function append(IModel $entity);

    /**
     * Removes a model.
     *
     * @param  IModel $entity The model to be removed.
     * @return self
     */
    public function drop(IModel $entity);

    /**
     * Removes every model inside.
     *
     * @return self
     */
    public function clear();

    /**
     * Crops the set.
     *
     * @param  int  $offset The beginning offset to be kept.
     * @param  int  $length OPTIONAL. The length to be kept. In defaults, every
     *                      model sould be kept until the end.
     * @return self
     */
    public function crop($offset, $length = 0);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
