<?php
/**
 * Defines the abstract output of applications.
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

namespace Tox\Application\Input;

use Tox\Core;
use Tox\Application;

/**
 * Represents as the abstract input of applications.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * @package tox.application.input
 * @author  Mark Snoopy <marksnoopy@gmail.com>
 */
abstract class Input extends Core\Assembly implements Application\IInput
{

    protected $_default;

    public function __construct()
    {

    }

    public function defaults($key, $value) {
        $this->_default[$key] = $value;
    }
}
