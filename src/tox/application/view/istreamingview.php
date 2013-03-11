<?php
/**
 * Defines the behaviors of interactive streaming views.
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

use Tox\Application;

/**
 * Announces the behaviors of interactive streaming views.
 *
 * @package tox.application.view
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
interface IStreamingView extends IView
{
    /**
     * CONSTRUCT FUNCTION
     *
     * @param Application\IOutput $output The corresponding output component.
     */
    public function __construct(Application\IOutput $output);

    /**
     * Appends a new blob for streaming.
     *
     * @param  string         $blob New blob to be appended.
     * @return IStreamingView
     */
    public function append($blob);
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
