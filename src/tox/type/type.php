<?php
/**
 * Defines the boxable objects.
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

use Tox\Core;

/**
 * Represents as the boxable objects.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * **NOTICE: Boxed variables would not be set free on `unset()` statements,
 * until another more `Varbase::gc()` has invoked.**
 *
 * @property-read mixed $value Retrieves the internal scalar value.
 *
 * @package tox.type
 * @author  Artur Graniszewski <aargoth@boo.pl>
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class Type extends Core\Assembly implements IBoxable
{
    /**
     * Stores the internal scalar value.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Stores the related runtime variables manager.
     *
     * @var IVarbase
     */
    protected $varbase;

    /**
     * Stores the reference ID.
     *
     * @var string
     */
    protected $ref;

    /**
     * CONSTRUCT FUNCTION
     *
     * NOTICE: Types validating progress would be done here.
     *
     * @param mixed $value Internal scalar value.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * DESTRUCT FUNCTION
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     */
    final public function __destruct()
    {
        if (!$this->varbase instanceof IVarbase || !isset($this->varbase[$this->ref])) {
            return;
        }
        if (is_scalar($this->varbase[$this->ref])) {
            $this->varbase[$this->ref] = new static($this->varbase[$this->ref]);
        }
        // TODO types casting of boxable objects.
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return string
     */
    final public function getRef()
    {
        return $this->ref;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @param  IVarbase $varbase Variables manager.
     * @return string
     */
    final public function setRef(IVarbase $varbase)
    {
        if ($this->varbase instanceof IVarbase) {
            throw new VariableReTeeingException;
        }
        $this->varbase = $varbase;
        $this->ref = $varbase->feedRef();
        return $this->ref;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return mixed
     */
    final public function getValue()
    {
        return $this->__getValue();
    }

    /**
     * Be invoked to retrieve the internal scalar value.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return mixed
     */
    final protected function __getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return string
     */
    final public function __toString()
    {
        return (string) $this->value;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  mixed $value Internal scalar value.
     * @return self
     */
    final public static function & box($value)
    {
        $o_var = & Varbase::tee(new static($value));
        return $o_var;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
