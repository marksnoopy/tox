<?php
/**
 * Defines the root exception for all derived ones to provide essential
 * behaviors.
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

namespace Tox\Core;

use Exception as PHPException;

class_alias('Tox\\Core\\Exception', 'Tox\\Exception');

/**
 * Represents as the root exception for all derived ones to provide essential
 * behaviors.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * @package   tox.core
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class Exception extends PHPException
{
    /**
     * Presents the index code of the exception type in the RUNTIME COMPLETE
     * EXCEPTIONS SHEET.
     *
     * @var int
     */
    const CODE = 0x80000000;

    /**
     * Presents the original standard message of the exception type.
     *
     * @var string
     */
    const MESSAGE = 'unknown exception';

    /**
     * Stores the context metas information.
     *
     * @var mixed[]
     */
    protected $context;

    /**
     * CONSTRUCT FUNCTION
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param array        $context OPTIONAL. Context metas information when the
     *                              exception raising.
     * @param PHPException $prevEx  Previous exception to be linked.
     */
    final public function __construct($context = array(), PHPException $prevEx = NULL)
    {
        if ($context instanceof PHPException) {
            $prevEx = $context;
            $context = array();
        } else {
            $context = (array) $context;
        }
        $this->context = $context;
        parent::__construct($this->figureReason(), static::CODE & 0x0fffffff, $prevEx);
    }

    /**
     * Figures the instantiated reason out for the raising exception.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return string
     */
    final protected function figureReason()
    {
        $a_phases = $a_values = array();
        preg_match_all('@%([^\\$]+)\\$@', static::MESSAGE, $a_phases);
        for ($ii = 0, $jj = count($a_phases[1]); $ii < $jj; $ii++) {
            $a_values[] = array_key_exists($a_phases[1][$ii], $this->context) ?
                $this->context[$a_phases[1][$ii]] :
                null;
            $a_phases[1][$ii] = '%' . (1 + $ii) . '$';
        }
        return ucfirst(vsprintf(str_replace($a_phases[0], $a_phases[1], static::MESSAGE), $a_values)) . '.';
    }

    /**
     * Retrieves the context metas information.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return mixed[]
     */
    final public function getContext()
    {
        return $this->context;
    }

    /**
     * Be invoked on string type casting.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return string
     */
    final public function __toString()
    {
        return sprintf('0x%08X: %s', static::CODE, $this->getMessage());
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
