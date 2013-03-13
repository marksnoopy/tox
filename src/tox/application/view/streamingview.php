<?php
/**
 * Defines the interactive streaming view.
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
 * Represents as the interactive streaming view.
 *
 * @package tox.application.view
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class StreamingView extends View implements Application\IStreamingView
{
    /**
     * Stores the buffered streaming blobs.
     *
     * @var string[]
     */
    protected $buffer;

    /**
     * Stores the corresponding output component.
     *
     * @var Application\IOutput
     */
    protected $output;

    /**
     * {@inheritdoc}
     *
     * @param Application\IOutput $output The corresponding output component.
     */
    public function __construct(Application\IOutput $output)
    {
        $this->buffer = array();
        $this->output = $output;
        parent::__construct(array());
    }

    /**
     * {@inheritdoc}
     *
     * @param  string        $blob New streaming blob to be appended.
     * @return StreamingView
     */
    public function append($blob)
    {
        $this->buffer[] = (string) $blob;
        $this->output->notifyStreaming();
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function render()
    {
        $s_blob = implode(PHP_EOL, $this->buffer);
        $this->buffer = array();
        return $s_blob;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
