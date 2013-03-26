<?php
/**
 * Defines the essential behaviors of applications.
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

use Exception;

use Tox\Core;

use PHPUnit_Framework_Error;
use PHPUnit_Framework_Exception;

/**
 * Represents as the abstract application to provide essential behaviors.
 *
 * **THIS CLASS CANNOT BE INSTANTIATED.**
 *
 * __*ALIAS*__ as `Tox\Application`.
 *
 * @property-read IConfiguration $config Retrieves the configuration.
 * @property-read IInput         $input  Retrieves the input.
 * @property-read IOutput        $output Retrieves the output.
 *
 * @package tox.application
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 * @since   0.1.0-beta1
 */
abstract class Application extends Core\Assembly
{
    /**
     * Stores the configuration.
     *
     * @var IConfiguration
     */
    protected $config;

    /**
     * Stores the falling back view.
     *
     * @var IFallback
     */
    protected $fallback;

    /**
     * Stores the input.
     *
     * @var IInput
     */
    protected $input;

    /**
     * Stores the runtime instance.
     *
     * @var self
     */
    protected static $instance;

    /**
     * Stores the output.
     *
     * @var IOutput
     */
    protected $output;

    /**
     * CONSTRUCT FUNCTION
     */
    protected function __construct()
    {
    }

    /**
     * Retrieves the default input on demand.
     *
     * **THIS METHOD MUST BE IMPLEMENTED.**
     *
     * @return IInput
     */
    abstract protected function getDefaultInput();

    /**
     * Retrieves the default output on demand.
     *
     * **THIS METHOD MUST BE IMPLEMENTED.**
     *
     * @return IOutput
     */
    abstract protected function getDefaultOutput();

    /**
     * Retrieves the default configuration on demand.
     *
     * @param  string         $file External configuration file path.
     * @return IConfiguration
     */
    protected function getDefaultConfiguration($file)
    {
        return new Configuration($file);
    }

    /**
     * Retrieves the default router on demand.
     *
     * @return IRouter
     *
     * @throws InvalidConfiguredRouterTypeException If configured router type is
     *                                              invalid.
     */
    protected function getDefaultRouter()
    {
        if (isset($this->config['router.type'])) {
            if (!is_subclass_of($this->config['router.type'], 'Tox\\Application\\IRouter')) {
                throw new InvalidConfiguredRouterTypeException(array('type' => $this->config['router.type']));
            }
            $o_router = new $this->config['router.type'];
        } else {
            $o_router = new Router\Router;
        }
        // TODO support routes table files.
        return $o_router;
    }

    /**
     * Retrieves the default falling back view on demand.
     *
     * @return IRouter
     *
     * @throws InvalidConfiguredFallbackTypeException If configured falling back
     *                                                view is invalid.
     */
    protected function getDefaultFallback()
    {
        if (isset($this->config['fallback.type'])) {
            if (!is_subclass_of($this->config['fallback.type'], 'Tox\\Application\\IFallback')) {
                throw new InvalidConfiguredFallbackTypeException(array('type' => $this->config['fallback.type']));
            }
            $o_fb = new $this->config['fallback.type'];
        } else {
            $o_fb = new View\Fallback;
        }
        return $o_fb;
    }

    /**
     * Retrieves the configuration.
     *
     * @return IConfiguration
     */
    public function getConfig()
    {
        return $this->__getConfig();
    }

    /**
     * Be invoked on retrieving the configuration.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return IConfiguration
     */
    final protected function __getConfig()
    {
        return $this->config;
    }

    /**
     * Retrieves the input.
     *
     * @return IInput
     */
    public function getInput()
    {
        return $this->__getInput();
    }

    /**
     * Be invoked on retrieving the input.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return IInput
     */
    final protected function __getInput()
    {
        return $this->input;
    }

    /**
     * Retrieves the output.
     *
     * @return IOutput
     */
    public function getOutput()
    {
        return $this->__getOutput();
    }

    /**
     * Be invoked on retrieving the output.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @internal
     *
     * @return IOutput
     */
    final protected function __getOutput()
    {
        return $this->output;
    }

    /**
     * Initializes the appliance runtime.
     *
     * **THIS METHOD MUST BE IMPLEMENTED.**
     *
     * @return self
     */
    abstract protected function init();

    /**
     * Runs the appliance.
     *
     * **THIS METHOD CANNOT BE OVERRIDDEN.**
     *
     * @param  IConfiguration|string $config   Configuration instance or file
     *                                         path.
     * @param  IRouter               $router   Router.
     * @param  IFallback             $fallback Falling back view.
     * @return void
     */
    final public static function run($config = null, IRouter $router = null, IFallback $fallback = null)
    {
        try {
            $o_app = static::getInstance();
            $o_app->config =
            $config = ($config instanceof IConfiguration) ? $config : $o_app->getDefaultConfiguration($config);
            $o_app->fallback =
            $fallback = (null === $fallback) ? $o_app->getDefaultFallback() : $fallback;
            if (isset($config['output.type'])) {
                if (!is_subclass_of($config['output.type'], 'Tox\\Application\\IOutput')) {
                    throw new InvalidConfiguredOutputTypeException(array('type' => $config['output.type']));
                }
                $o_app->output = new $config['output.type'];
            } else {
                $o_app->output = $o_app->getDefaultOutput();
            }
            if (isset($config['input.type'])) {
                if (!is_subclass_of($config['input.type'], 'Tox\\Application\\IInput')) {
                    throw new InvalidConfiguredInputTypeException(array('type' => $config['input.type']));
                }
                $o_app->input = new $config['input.type'];
            } else {
                $o_app->input = $o_app->getDefaultInput();
            }
            $o_app->init();
            $o_app->dispatch($o_app->route((null === $router) ? $o_app->getDefaultRouter() : $router))->act();
        } catch (Exception $ex) {
            if ($ex instanceof PHPUnit_Framework_Error || $ex instanceof PHPUnit_Framework_Exception) {
                throw $ex;
            }
            if (null === $fallback) {
                $fallback = self::$instance->getDefaultFallback();
            }
            $o_out = (self::$instance->output instanceof IOutput) ?
                self::$instance->output :
                self::$instance->getDefaultOutput();
            $o_out->setView($fallback->cause($ex))->close();
        }
    }

    /**
     * Retrieves the only runtime instance.
     *
     * @return self
     *
     * @throws MultipleApplicationRuntimeException If there is already another
     *                                             appliance is running.
     */
    protected static function getInstance()
    {
        if (self::$instance instanceof self)
        {
            throw new MultipleApplicationRuntimeException(array('existant' => self::$instance));
        }
        self::$instance = new static;
        return self::$instance;
    }

    /**
     * Routes the appliance.
     *
     * @param  IRouter $router Router.
     * @return IToken
     */
    protected function route(IRouter $router)
    {
        $o_token = $router->analyse($this->input);
        $this->input->recruit($o_token);
        return $o_token;
    }

    /**
     * Dispatches to a corresponding controller.
     *
     * @param  IToken      $token Dispatching token.
     * @return IController
     */
    protected function dispatch(IToken $token)
    {
        $s_ctrl = $token->getController();
        return new $s_ctrl($this);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
