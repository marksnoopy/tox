<?php
/**
 * Provides behaviors to all derived applications.
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
 * @package   Tox
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Application;

use Exception;

use Tox\Core;

abstract class Application extends Core\Assembly
{
    protected $config;

    protected $fallback;

    protected $input;

    protected static $instance;

    protected $output;

    protected function __construct(IInput $input = NULL, IOutput $output = NULL)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function fallback(Exception $ex)
    {
        $this->output->writeClose($this->fallback($ex));
        return $this;
    }

    abstract protected static function getDefaultInput();

    abstract protected static function getDefaultOutput();

    protected function __getConfig()
    {
        return $this->config;
    }

    protected function __getInput()
    {
        return $this->input;
    }

    protected function __getOutput()
    {
        return $this->output;
    }

    abstract protected function init();

    final public static function run(
        IConfiguration $config = NULL,
        IRouter $router = NULL,
        View\IFallback $fallback = NULL
    )
    {
        if (self::$instance instanceof self)
        {
            self::$instance->output->writeClose(
                self::$instance->fallback(
                    new MultipleApplicationRuntimeException(array('existant' => self::$instance))
                )
            );
            return;
        }
        if (NULL === $config)
        {
            $config = new Configuration;
        }
        if (NULL === $router)
        {
            $router = new Router;
        }
        if (NULL === $fallback)
        {
            $fallback = new View\Fallback;
        }
        $o_output = isset($config['output']) && is_subclass_of($config['output'], '\\IOutput')
            ? new $config['output']
            : static::getDefaultOutput();
        try
        {
            if (isset($config['input']))
            {
                if (!is_subclass_of($config['input'], '\\IInput'))
                {
                    throw new InvalidInputComponentException(array('input' => $config['input']));
                }
                $o_input = new $config['input'];
            }
            else
            {
                $o_input = static::getDefaultInput();
            }
            self::$instance = new static($o_input, $o_output);
            self::$instance->config = $config;
            self::$instance->fallback = $fallback;
            self::$instance->init();
            $o_token = $router->import($config->export('route.*', array()), TRUE)->analyse($o_input);
            self::$instance->input->recruit($o_token);
			$s_ctrl = $o_token->getController();
            $o_controller = new $s_ctrl(self::$instance);
            $o_controller->act();
        }
        catch (Exception $ex)
        {
            // DEBUG
            die('<table>' . $ex->xdebug_message .'</table><pre>' . $ex->getTraceAsString() . '</pre>');
            $o_output->writeClose($fallback($ex));
        }
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
