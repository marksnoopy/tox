<?php
/**
 * Defines the essential behaviors of applications output.
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

/**
 * Announces the essential behaviors of applications output.
 *
 * @package tox.application
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
interface IOutput
{
    /**
     * Closes and stops further outputing blobs.
     *
     * @return self
     */
    public function close();

    /**
     * Outputs a blob.
     *
     * @param  string $blob New blob to be outputed.
     * @return self
     */
    public function write($blob);

    /**
     * Outputs a blob and then closes.
     *
     * @param  string $blob New blob to be outputed.
     * @return self
     */
    public function writeClose($blob);

    /**
     * Be invoked on streaming new blobs.
     *
     * @return self
     */
    public function notifyStreaming();

    /**
     * Enables interactive streaming mode to output buffering blobs immediately.
     *
     * @return self
     */
    public function enableStreaming();

    /**
     * Disables interactive streaming mode to output all buffering blobs once at
     * last.
     *
     * @return self
     */
    public function disableStreaming();

    /**
     * Checks whether in the interactive streaming mode.
     *
     * @return bool
     */
    public function isStreaming();

    /**
     * Adds an outputting task.
     *
     * @param  Output\ITask $task New outputting task.
     * @return self
     */
    public function addTask(IOutputTask $task);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
