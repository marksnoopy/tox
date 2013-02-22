<?php
/**
 * Provides behaviors to all derived exceptions.
 *
 * This class cannot be instantiated.
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
 * @package   Tox\Web
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox;

use Exception as Ex;

abstract class Exception extends Ex
{
    const CODE = 0x80000000;

    protected static $TEMPLATE = 'unknown exception';

    protected $context;

    final public function __construct($context = array(), Ex $prevEx = NULL)
    {
        if ($context instanceof Ex)
        {
            $prevEx = $context;
            $context = array();
        }
        else
        {
            settype($context, 'array');
        }
        $this->context = $context;
        $s_msg = static::$TEMPLATE;
        if (!empty($context))
        {
            $a_holders = array();
            preg_match_all('@%([^\$]+)\$@', static::$TEMPLATE, $a_holders);
            $a_values = array();
            for ($ii = 0, $jj = count($a_holders[0]); $ii < $jj; $ii++)
            {
                if (!array_key_exists($a_holders[1][$ii], $context))
                {
                    $s_msg = "context '{$a_holders[1][$ii]}' missing";
                    break;
                }
                $a_values[] = $context[$a_holders[1][$ii]];
                $a_holders[1][$ii] = '%' . (1 + $ii) . '$';
            }
            $s_msg = vsprintf(str_replace($a_holders[0], $a_holders[1], static::$TEMPLATE), $a_values);
        }
        parent::__construct(ucfirst($s_msg) . '.', static::CODE & 0x0fffffff, $prevEx);
    }

    final public function getContext()
    {
        return $this->context;
    }

    final public function __toString()
    {
        return sprintf('0x%08X', static::CODE) . ': ' . $this->message;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
