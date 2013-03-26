<?php
/**
 * Defines the data access objects.
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

namespace Tox\Application\Dao;

use Tox\Core;
use Tox\Data;
use Tox\Application;

/**
 * Represents as a data access object.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * __*ALIAS*__ as `Tox\Application\Dao`.
 *
 * @package tox.application.dao
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
abstract class Dao extends Core\Assembly implements Application\IDao
{
    /**
     * Stores the binded domains for all derived data access objects.
     *
     * @internal
     *
     * @var Data\ISource[]
     */
    protected static $domains = array();

    /**
     * Stores the instances of all derived data access objects.
     *
     * @internal
     *
     * @var self[]
     */
    protected static $instances = array();

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  Data\ISource $domain Data domain to be binded.
     * @return void
     */
    final public static function bindDomain(Data\ISource $domain)
    {
        self::$domains[get_called_class()] = $domain;
    }

    /**
     * Retrieves the most suitable data domain.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * NOTICE: MOST SUITABLE means that the data domain was binded by the type
     * of current invoker, or its most recently parent.
     *
     * @return Data\ISource
     */
    final protected function getDomain()
    {
        $s_class = get_called_class();
        while (FALSE !== $s_class)
        {
            if (isset(self::$domains[$s_class]))
            {
                return self::$domains[$s_class];
            }
            $s_class = get_parent_class($s_class);
        }
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     */
    final public static function getInstance()
    {
        $s_class = get_called_class();
        if (!isset(self::$instances[$s_class])) {
            self::$instances[$s_class] = new $s_class;
        }
        return self::$instances[$s_class];
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
