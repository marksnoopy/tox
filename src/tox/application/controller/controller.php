<?php
/**
 * Defines the abstract controller of applications.
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

namespace Tox\Application\Controller;

use Tox\Core;
use Tox\Application;

/**
 * Represents as the abstract controller of applications.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * __*ALIAS*__ as `Tox\Application\Controller`.
 *
 * @package tox.application.controller
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
abstract class Controller extends Core\Assembly
{
    /**
     * Stores the hosting application.
     *
     * @var Application\Application
     */
    protected $application;

    /**
     * Stores the runtime configuration of hosting application.
     *
     * @var Application\IConfiguration
     */
    protected $config;

    /**
     * Stores the inputting pipe of hosting application.
     *
     * @var Application\IInput
     */
    protected $input;

    /**
     * Stores the outputting pipe of hosting application.
     *
     * @var Application\IOutput
     */
    protected $output;

    /**
     * {@inheritdoc}
     *
     * @param Application\Application $application Hosting application.
     */
    public function __construct(Application\Application $application)
    {
        $this->application = $application;
        $this->config = $application->config;
        $this->input = $application->input;
        $this->output = $application->output;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
