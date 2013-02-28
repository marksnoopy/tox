<?php
/**
 * Defines the APIs to control applications runtime environments which based on
 * `Tox'.
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

namespace Tox\Core;

use Exception;

class_alias('Tox\\Core\\Runtime', 'Tox');

/**
 * Provides the APIs to control applications runtime environments.
 *
 * THIS CLASS CAN BE NEITHER INHERITED NOR INSTANTIATED.
 *
 * @package   tox.core
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 */
final class Runtime
{
    /**
     * Stores the instance of the classes manager of framework runtime.
     *
     * @var ClassManager
     *
     * @internal
     */
    private $classManager;

    /**
     * Stores the instance of current APIs provider.
     *
     * @var Runtime
     *
     * @internal
     */
    private static $instance;

    /**
     * Stores the instance of the packages manager of framework runtime.
     *
     * @var PackageManager
     *
     * @internal
     */
    private $packageManager;

    /**
     * CONSTRUCT FUNCTION
     */
    private function __construct()
    {
        $this->classManager = new ClassManager;
        $this->packageManager = new PackageManager;
    }

    /**
     * AUTOLOADs the required class or interface.
     *
     * @param  string $class Name of specified class or interface.
     * @return void
     *
     * @internal
     */
    public function load($class)
    {
        settype($class, 'string');
        $s_class = $this->classManager->transform($class);
        $p_class = $this->packageManager->locateClass($s_class);
        if (!is_string($p_class)) {
            return;
        }
        require_once($p_class);
        if (class_exists($class, false)) {
            $this->classManager->register($class, $p_class);
        }
    }

    /**
     * Imports a new namespace to the path of eithor a directory or a PHar file.
     *
     * @param  string $namespace Namespace to be imported.
     * @param  string $path      The corresponding path.
     * @return void
     *
     * @api
     */
    public static function import($namespace, $path)
    {
        static::setUp();
        try {
            static::$instance->packageManager->registerPackage(str_replace('\\', '.', strtolower($namespace)), $path);
        } catch (Exception $ex) {
            trigger_error('Tox: ' . $ex->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * Alias of `import()'.
     *
     * @deprecated Remained for forward compatibility. Would be removed in some
     *             future version.
     *
     * @param  string $namespace Namespace to be imported.
     * @param  string $path      The corresponding path.
     * @return void
     *
     * @see Runtime::import()
     *
     * @api
     */
    public static function registerNamespace($namespace, $path)
    {
        return self::import($namespace, $path);
    }

    /**
     * Sets the framework runtime environment up.
     *
     * @return void
     *
     * @api
     */
    public static function setUp()
    {
        if (static::$instance instanceof static) {
            return;
        }
        static::$instance = new static;
        spl_autoload_register(array(static::$instance, 'load'), true, true);
    }

    /**
     * Aliases either a class or an interface to another.
     *
     * @param  string $class Name of either a class or an interface.
     * @param  string $alias New alias name.
     * @return void
     *
     * @api
     */
    public static function alias($class, $alias)
    {
        static::setUp();
        try {
            static::$instance->classManager->treatAs($class, $alias);
        } catch (Exception $ex) {
            trigger_error('Tox: ' . $ex->getMessage(), E_USER_ERROR);
        }
    }

    /**
     * Alias of `alias()'.
     *
     * @deprecated Remained for forward compatibility. Would be removed in some
     *             future version.
     *
     * @param  string $class   Name of either a class or an interface.
     * @param  string $classAs New alias name.
     * @return void
     *
     * @see Runtime::alias()
     *
     * @api
     */
    public static function treatClassAs($class, $classAs)
    {
        return self::alias($class, $classAs);
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
