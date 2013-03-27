<?php
/**
 * Defines the runtime variables manager.
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
 * Represents as the runtime variables manager.
 *
 * **WARNING: THIS CLASS IS DESIGNED TO MAINTAIN BOXABLE OBJECTS. DO NOT DO
 * ANYTHING TO CHANGE IT UNLESS YOU ARE VERY SURE ABOUT YOUR PURPOSES AND ITS
 * MECHANISM.**
 *
 * @package tox.type
 * @author  Artur Graniszewski <aargoth@boo.pl>
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.2.0
 */
class Varbase extends Core\Assembly implements IVarbase
{
    /**
     * Stores the only instance.
     *
     * @var self
     */
    protected static $instance;

    /**
     * Stores the maintained variables.
     *
     * @var IBoxable[]
     */
    protected $vars;

    /**
     * Stores the boxable types of variables.
     *
     * @var string[]
     */
    protected $types;

    /**
     * Stores the seed to generate reference IDs.
     *
     * @var string
     */
    protected $seed;

    /**
     * Stores the stacking reference ID to feed out.
     *
     * @var string
     */
    protected $feed;

    /**
     * CONSTRUCT FUNCTION
     */
    protected function __construct()
    {
        $this->vars =
        $this->types = array();
        $this->seed = microtime();
        $this->feed = '';
    }

    /**
     * {@inheritdoc}
     *
     * @param  IBoxable $var Boxable object to be maintained.
     * @return IBoxable
     */
    public static function & tee(IBoxable $var)
    {
        if (!self::$instance instanceof self) {
            self::$instance = new static;
        }
        $s_ref = $var->setRef(self::$instance);
        self::$instance->vars[$s_ref] = $var;
        self::$instance->types[$s_ref] = get_class($var);
        return self::$instance->vars[$s_ref];
    }

    /**
     * {@inheritdoc}
     *
     * NOTICE: This method would not work well until the boxed objects have
     * already been `unset()`.
     *
     *     $o_var = & String::box('foo');
     *     unset($o_var);
     *     Varbase::gc();
     *
     * @return void
     */
    public static function gc()
    {
        if (!self::$instance instanceof self) {
            return;
        }
        $a_list = array();
        reset(self::$instance->vars);
        while (list($s_ref, ) = each(self::$instance->vars)) {
            ob_start();
            debug_zval_dump(self::$instance->vars[$s_ref]);
            $s_dump = ob_get_clean();
            if (preg_match('@\) refcount\((\d+)\)\{\n@', $s_dump, $a_matches)) {
                $i_refs = (int) $a_matches[1];
            } else {
                $i_refs = 2;
            }
            if (2 == $i_refs) {
                $a_list[] = $s_ref;
            }
        }
        for ($ii=0, $jj = count($a_list); $ii < $jj; $ii++) {
            unset(self::$instance->vars[$a_list[$ii]]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function feedRef()
    {
        if (strlen($this->feed)) {
            $s_ref = $this->feed;
            $this->feed = '';
        } else {
            do {
                $s_ref = sha1(microtime() . $this->seed);
            } while ($this->offsetExists($s_ref));
        }
        return $s_ref;
    }

    /**
     * Be invoked on checking the existance of a variable.
     *
     * @param  string $ref Reference ID of that variable.
     * @return bool
     */
    public function offsetExists($ref)
    {
        return array_key_exists((string) $ref, $this->vars);
    }

    /**
     * Be invoked on retrieving a variable.
     *
     * @param  string        $ref Reference ID of that variable.
     * @return IBoxable|null
     */
    public function offsetGet($ref)
    {
        if (!$this->offsetExists($ref)) {
            return null;
        }
        return $this->vars[(string) $ref];
    }

    /**
     * Be invoked on reboxing a variable.
     *
     * @param  string   $ref Reference ID of that variable.
     * @param  IBoxable $var New boxed variable.
     * @return void
     */
    public function offsetSet($ref, $var)
    {
        $ref = (string) $ref;
        if (!$this->offsetExists($ref) ||
            !$var instanceof IBoxable ||
            get_class($var) != $this->types[$ref] ||
            $var->getValue() != $this->offsetGet($ref)
        ) {
            throw new VarbaseUnderAttackException;
        }
        $this->feed = $ref;
        $var->setRef($this);
        $this->vars[$ref] = $var;
    }

    /**
     * Be invoked on garbage collecting a variable.
     *
     * @param  string $ref Reference ID of that variable.
     * @return void
     */
    public function offsetUnset($ref)
    {
    }

    /**
     * Retrieves the amount of maintained variables.
     *
     * @return int
     */
    public function count()
    {
        return count($this->vars);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
