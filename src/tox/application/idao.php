<?php
/**
 * Defines the essential behaviors of data access objects.
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

namespace Tox\Application;

use Tox\Core;
use Tox\Data;

/**
 * Announces the essential behaviors of data access objects.
 *
 * @package tox.application
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
interface IDao extends Core\ISingleton
{
    /**
     * Binds a data domain.
     *
     * NOTICE: To use a data domain for all derived data access objects, binds
     * that on the userland abstract data access object.
     *
     * @param  Data\ISource $domain Data domain to be binded.
     * @return void
     */
    public static function bindDomain(Data\ISource $domain);

    /**
     * Retrieves the amount of matched data records.
     *
     * @param  array   $conditions OPTIONAL. Filter conditions. An empty array
     *                             defaults.
     * @param  integer $offset     OPTIONAL. Offset of counting to the first
     *                             matched data record. 0 defaults.
     * @param  integer $length     OPTIONAL. Maximize amount of matched data
     *                             records. 0 defaults as unlimited.
     * @return integer
     */
    public function countBy($conditions = array(), $offset = 0, $length = 0);

    /**
     * Creates a data record and returns its ID.
     *
     * @param  mixed[] $fields Fields and Values for the record.
     * @return string
     */
    public function create($fields);

    /**
     * Deletes a data record.
     *
     * @param  string $id Record ID.
     * @return self
     */
    public function delete($id);

    /**
     * Retrieves matched data records in order.
     *
     * @param  array   $conditions OPTIONAL. Filter conditions. An empty array
     *                             defaults.
     * @param  array   $sorts      OPTIONAL. Sorting fields. An empty array
     *                             defaults.
     * @param  integer $offset     OPTIONAL. Offset of retrieving to the first
     *                             matched data record. 0 defaults.
     * @param  integer $length     OPTIONAL. Maximize amount of matched data
     *                             records. 0 defaults as unlimited.
     * @return array[]
     */
    public function listBy($conditions = array(), $sorts = array(), $offset = 0, $length = 0);

    /**
     * Reads a data record.
     *
     * @param  string $id Record ID.
     * @return array
     */
    public function read($id);

    /**
     * Updates a data record.
     *
     * @param  string  $id     Record ID.
     * @param  mixed[] $fields Changed fields and values for the record.
     * @return self
     */
    public function update($id, $fields);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
