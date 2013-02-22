<?php
/**
 * Provides APIs to controll the runtime environment.
 *
 * This class cannot be inherited.
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

final class Tox
{
    private $classManager;

    private static $instance;

    private $packageManager;

    private function __construct()
    {
        $this->classManager = new Tox\Core\ClassManager;
        $this->packageManager = new Tox\Core\PackageManager;
    }

    public function load($class)
    {
        settype($class, 'string');
        $s_class = $this->classManager->transform($class);
        $p_class = $this->packageManager->locateClass($s_class);
        if (!is_string($p_class))
        {
            return;
        }
        require_once($p_class);
        if (class_exists($class, FALSE))
        {
            $this->classManager->register($class, $p_class);
        }
    }

    private static function registerAutoloader()
    {
        spl_autoload_register(array(static::$instance, 'load'), TRUE, TRUE);
    }

    public static function registerNamespace($namespace, $path)
    {
        static::setUp();
        try
        {
            static::$instance->packageManager->registerPackage(str_replace('\\', '.', strtolower($namespace)), $path);
        }
        catch (Exception $ex)
        {
            trigger_error('Tox: ' . $ex->getMessage(), E_USER_ERROR);
        }
    }

    public static function setUp()
    {
        if (static::$instance instanceof static)
        {
            return;
        }
        static::$instance = new static;
        static::registerAutoloader();
    }

    public static function treatClassAs($class, $classAs)
    {
        static::setUp();
        try
        {
            static::$instance->classManager->treatAs($class, $classAs);
        }
        catch (Exception $ex)
        {
            trigger_error('Tox: ' . $ex->getMessage(), E_USER_ERROR);
        }
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
