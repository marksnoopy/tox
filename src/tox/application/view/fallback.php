<?php
/**
 * Defines the fallback view.
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

namespace Tox\Application\View;

use Exception;

use Tox\Application;

/**
 * Represents as the fallback view.
 *
 * @package tox.application.view
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
class Fallback extends View implements Application\IFallback
{
    /**
     * Stores the rendered result buffer.
     *
     * @var string
     */
    protected $buffer;

    /**
     * CONSTRUCT FUNCTION
     */
    public function __construct()
    {
        parent::__construct();
        $this->buffer = false;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  Exception $exception Cause exception.
     * @return self
     */
    final public function cause(Exception $exception)
    {
        if (!isset($this->metas['exception'])) {
            $this->metas['exception'] = $exception;
            $this->render();
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function render()
    {
        if (false === $this->buffer) {
            $this->buffer = 'Tox: ' . $this->dumpException($this->metas['exception']);
        }
        return $this->buffer;
    }

    /**
     * Dumps the cause exception and its linked previous exceptions.
     *
     * @internal
     *
     * @param  Exception $exception The exception to be dumped.
     * @return string
     */
    protected function dumpException(Exception $exception)
    {
        $s_lob = $exception->getMessage() . PHP_EOL .
            ' [ #' . $exception->getLine() . ' of ' . $exception->getFile() . ' ]' . PHP_EOL;
        foreach ($exception->getTrace() as $a_line) {
            $s_lob .= '> ';
            if (isset($a_line['class'])) {
                $s_lob .= $a_line['class'] . $a_line['type'];
            }
            $s_lob .= $a_line['function'] . '(';
            for ($ii = 0, $jj = count($a_line['args']); $ii < $jj; $ii++) {
                if (is_object($a_line['args'][$ii])) {
                    $a_line['args'][$ii] = get_class($a_line['args'][$ii]);
                } elseif (is_array($a_line['args'][$ii])) {
                    $a_line['args'][$ii] = 'Array';
                } elseif (is_string($a_line['args'][$ii])) {
                    $a_line['args'][$ii] = "'{$a_line['args'][$ii]}'";
                }
            }
            if ($jj) {
                $s_lob .= ' ' . implode(' , ', $a_line['args']) . ' ';
            }
            $s_lob .= ')' . PHP_EOL;
            if (isset($a_line['file'])) {
                $s_lob .= '   [ #' . $a_line['line'] . ' of ' . $a_line['file'] . ' ]' . PHP_EOL;
            }
        }
        if ($exception->getPrevious() instanceof Exception) {
            $s_lob .= PHP_EOL . '@@@@ ' . $this->dumpException($exception->getPrevious());
        }
        return $s_lob;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
