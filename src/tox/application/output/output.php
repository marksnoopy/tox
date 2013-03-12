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
     * CONSTRUCT FUNCTION
     *
     * @internal
     */
    public function __construct()
    {
        $this->closed =
        $this->streaming = false;
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
        $this->preOutput();
        print($this->__getView()->render());
        $this->postOutput();
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
        if (!$this->__getView() instanceof View\IStreamingView) {
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
        if ($this->closed || !$this->streaming) {
            return $this;
        }
        $this->preOutput();
        print($this->__getView()->render());
        $this->postOutput();
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
        if (!$this->__getView() instanceof View\IStreamingView) {
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
        if (!$this->__getView() instanceof View\IStreamingView) {
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
     * **THIS METHOD MUST BE IMPLEMENTED.**
     *
     * @return void
     */
    abstract protected function preOutput();

    /**
     * Be invoked after any blob being outputed.
     *
     * **THIS METHOD MUST BE IMPLEMENTED.**
     *
     * @return void
     */
    abstract protected function postOutput();
}
