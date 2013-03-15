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
 * @copyright © 2012-2013 SZen.in
 * @license   GNU General Public License, version 3
 */

namespace Tox\Application\Output;

use Tox;
use Tox\Application;
use Tox\Application\View;

/**
 * Represents as the abstract output of applications.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * __*ALIAS*__ as `Tox\Application\Output`.
 *
 * @property View\IView $view Retrieves and Sets the binded view.
 *
 * @package tox.application.output
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class Output extends Tox\Assembly implements Application\IOutput
{
    /**
     * Stores the binded view.
     *
     * @internal
     *
     * @var View\IView
     */
    protected $view;

    /**
     * Stores the status of streaming mode.
     *
     * @internal
     *
     * @var bool
     */
    protected $streaming;

    /**
     * Stores the working status.
     *
     * @internal
     *
     * @var bool
     */
    protected $closed;

    /**
     * Stores the scheduled outputting tasks.
     *
     * @var ITask[]
     */
    protected $tasks;

    /**
     * Stores the rendering result of the binded view.
     *
     * @var string
     */
    protected $buffer;

    /**
     * Stores the outputting status.
     *
     * @var bool
     */
    protected $outputting;

    /**
     * CONSTRUCT FUNCTION
     *
     * @internal
     */
    public function __construct()
    {
        $this->closed =
        $this->streaming =
        $this->outputting = false;
        $this->tasks = array();
        $this->buffer = '';
    }

    /**
     * Be invoked on retrieving the binded view.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return Application\IView
     */
    final protected function __getView()
    {
        if (null === $this->view) {
            $this->view = new View\StreamingView($this);
        }
        return $this->view;
    }

    /**
     * Be invoked on setting the binded view.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @param  Application\IView $view New binded view.
     * @return void
     */
    final protected function __setView(Application\IView $view)
    {
        $this->view = $view;
    }

    /**
     * {@inheritdoc}
     *
     * @return Application\IView
     */
    public function getView()
    {
        return $this->__getView();
    }

    /**
     * {@inheritdoc}
     *
     * @param  Application\IView $view New binded view.
     * @return self
     */
    public function setView(Application\IView $view)
    {
        $this->__setView($view);
        return $this;
    }

    /**
     * Be invoked on retrieving the rendering result of the binded view.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return string
     */
    final protected function __getBuffer()
    {
        return $this->buffer;
    }

    /**
     * Be invoked on changing the outputting buffer manually.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @param  string $blob New blob for outputting.
     * @return void
     *
     * @throws BufferReadonlyException If setting while not outputting.
     */
    final protected function __setBuffer($blob)
    {
        if (!$this->outputting) {
            throw new BufferReadonlyException;
        }
        $this->buffer = (string) $blob;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getBuffer()
    {
        return $this->__getBuffer();
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $blob New outputting buffer.
     * @return self
     *
     * @throws BufferReadonlyException If setting while not outputting.
     */
    public function setBuffer($blob)
    {
        $this->__setBuffer($blob);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     */
    final public function close()
    {
        if ($this->closed) {
            return $this;
        }
        $this->closed = true;
        $this->outputting = true;
        if (!$this->__getView() instanceof Application\IStreamingView) {
            $this->buffer = $this->__getView()->render();
        }
        print($this->preOutput()->buffer);
        $this->postOutput()->buffer = '';
        $this->outputting = false;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * NOTICE: On streaming mode, the blob would be outputed directly.
     *
     * @param  string $blob New blob to be outputed.
     * @return self
     *
     * @throws ClosedOutputException          If the output is already closed.
     * @throws StreamingViewExpectedException If the binded view is not for
     *                                        streaming.
     */
    final public function write($blob)
    {
        if ($this->closed) {
            throw new ClosedOutputException;
        }
        if (!$this->__getView() instanceof Application\IStreamingView) {
            throw new StreamingViewExpectedException;
        }
        $this->__getView()->append($blob);
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  string $blob New blob to be outputed.
     * @return self
     */
    final public function writeClose($blob)
    {
        return $this->write($blob)->close();
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     */
    final public function notifyStreaming()
    {
        if ($this->closed) {
            return $this;
        }
        $this->buffer .= $this->__getView()->render();
        if (!$this->streaming) {
            return $this;
        }
        $this->outputting = true;
        print($this->preOutput()->buffer);
        $this->postOutput()->buffer = '';
        $this->outputting = false;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     *
     * @throws StreamingViewExpectedException If the binded view is not for
     *                                        streaming.
     */
    final public function enableStreaming()
    {
        if (!$this->__getView() instanceof Application\IStreamingView) {
            throw new StreamingViewExpectedException;
        }
        $this->streaming = true;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     *
     * @throws StreamingViewExpectedException If the binded view is not for
     *                                        streaming.
     */
    final public function disableStreaming()
    {
        if (!$this->__getView() instanceof Application\IStreamingView) {
            throw new StreamingViewExpectedException;
        }
        $this->streaming = false;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return bool
     */
    final public function isStreaming()
    {
        return $this->streaming;
    }

    /**
     * Be invoked before any blob being outputed.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     */
    final protected function preOutput()
    {
        if (!$this->outputting) {
            return $this;
        }
        for ($ii = 0, $jj = count($this->tasks); $ii < $jj; $ii++) {
            $this->tasks[$ii]->preOutput();
        }
        return $this;
    }

    /**
     * Be invoked after any blob being outputed.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @return self
     */
    final protected function postOutput()
    {
        if (!$this->outputting) {
            return $this;
        }
        for ($ii = count($this->tasks) - 1; 0 <= $ii; $ii--) {
            $this->tasks[$ii]->postOutput();
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  Application\IOutputTask $task The task to be processed on
     *                                       outputting.
     * @return self
     */
    final public function addTask(Application\IOutputTask $task)
    {
        $this->tasks[] = $task;
        return $this;
    }
}
