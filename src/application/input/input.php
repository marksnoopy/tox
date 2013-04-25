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
use Tox\Type;

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
    /**
     * Stores the default value.
     *
     * @var mixed
     */
    protected $default;

    public function __construct()
    {
    }

    /**
     * set the default value for the specil input.
     *
     * @param  string $key
     * @param  mixed $value
     * @return Input
     */
    public function defaults($key, $value)
    {
        $this->default[$key] = $value;
        return $this;
    }

    /**
     * valid the type of the input' value is equal to the expected type or not.
     *
     * @param string $key
     * @param string $type
     * @return Input
     */
    public function expected($key, $type) {
        $a_type = $this->typeList();
        if(!((is_array($a_type)) && (isset($a_type[$type])) && class_exists($a_type[$type]))) {
            throw new UnknownTypeException;
        }
        new $a_type[$type]($this->offsetGet($key));
        return $this;
    }

    protected function typeList(){
        return array(
            'email' => 'Tox\Type\Simple\Email',
        );
    }
}
