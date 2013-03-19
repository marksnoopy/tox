<?php
/**
 * Defines the runtime packages manager.
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

/**
 * Represents as the runtime packages manager.
 *
 * @package tox.core
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class PackageManager extends Assembly
{
    /**
     * Stores the registered and probed packages.
     *
     * @internal
     *
     * @var array[]
     */
    protected $packages;

    /**
     * CONSTRUCT FUNCTION
     */
    public function __construct()
    {
        $this->packages = array();
    }

    /**
     * Locates where the assembly be put.
     *
     * NOTICE: The returning definition file has not been validated for
     * accessability.
     *
     * @param  string $class Assembly name.
     * @return string
     */
    public function locate($class)
    {
        $s_class = ltrim(str_replace('.', '\\', strtolower($class)), '\\');
        $a_class = explode('\\', $s_class);
        $p_file = array_pop($a_class);
        if (strrpos($p_file, 'exception')) {
            $p_file = '@exception/' . substr($p_file, 0, -9);
        }
        $p_file .= '.php';
        $a_extra = array();
        $i_offs = ('tox' == $a_class[0]) ? 1 : 2;
        $s_class = implode('\\', array_splice($a_class, 0, $i_offs));
        for ($ii = 0, $jj = count($a_class); $ii < $jj; $ii++) {
            $s_new = "{$s_class}\\{$a_class[$ii]}";
            if (!isset($this->packages[$s_new])) {
                if (!isset($this->packages[$s_class])) {
                    if (2 == $i_offs) {
                        // Higher-ups of registered 3rd-party package MAY not
                        // exist.
                        $s_class = $s_new;
                        continue;
                    }
                    return false;
                }
                if (2 == $i_offs) {
                    // Lower-downs of the root registered 3rd-party package MUST
                    // work as similarly as Tox packages.
                    $i_offs = 1;
                }
                $this->packages[$s_new] = array(
                    $this->packages[$s_class][0] . DIRECTORY_SEPARATOR . $a_class[$ii],
                    false
                );
            }
            $s_class = $s_new;
        }
        if (!isset($this->packages[$s_class]) || !$this->bootstrap($s_class)) {
            return false;
        }
        return $this->packages[$s_class][0] . DIRECTORY_SEPARATOR . $p_file;
    }

    /**
     * Registers a namespace.
     *
     * @param  string $namespace New namespace.
     * @param  string $path      Where the namespace be put.
     * @return self
     *
     * @throws PackageAccessDeniedException          If the path access denied.
     * @throws PackageDuplicateRegistrationException If registering an existant
     *                                               namespace.
     * @throws Illegal3rdPartyPackageException       If the 3rd-party namespace
     *                                               is illegal.
     */
    public function register($namespace, $path)
    {
        $s_ns = ltrim(str_replace('.', '\\', strtolower($namespace)), '\\');
        $path = (string) $path;
        if (!is_dir($path) || !is_readable($path)) {
            throw new PackageAccessDeniedException(array('path' => $path));
        }
        if (isset($this->packages[$s_ns])) {
            throw new PackageDuplicateRegistrationException(array('package' => $namespace));
        }
        $a_ns = explode('\\', $s_ns);
        if ('tox' != $a_ns[0] && 3 > count($a_ns)) {
            throw new Illegal3rdPartyPackageException(array('package' => $namespace));
        }
        $this->packages[$s_ns] = array($this->canonicalize($path), false);
        if ('tox\\core' == $s_ns && 'core' == basename($this->packages[$s_ns][0])) {
            $this->packages['tox'] = array(dirname($this->packages[$s_ns][0]), true);
        }
        return $this;
    }

    /**
     * Canonicalize the path.
     *
     * @internal
     *
     * @param  string $path Input path to be formatted.
     * @return string
     */
    protected function canonicalize($path)
    {
        $s_prefix = '';
        $i_pos = strpos($path, '://');
        if ($i_pos) {
            $s_prefix = substr($path, 0, 3 + $i_pos);
            $path = substr($path, 3 + $i_pos);
        }
        if ('/' == DIRECTORY_SEPARATOR) {
            $a_nodes = explode('/', str_replace('\\', '/', $path));
        } else {
            $a_nodes = explode('\\', str_replace('/', '\\', $path));
            $s_prefix .= array_shift($a_nodes);
        }
        $a_new = array();
        for ($ii = 0, $jj = count($a_nodes); $ii < $jj; $ii++) {
            switch ($a_nodes[$ii]) {
                case '..':
                    array_pop($a_new);
                case '.':
                    break;
                default:
                    $a_new[] = $a_nodes[$ii];
            }
        }
        return $s_prefix . implode(DIRECTORY_SEPARATOR, $a_new);
    }

    /**
     * Bootstraps the namespace and all its higher-ups.
     *
     * @internal
     *
     * @param  string $namespace Target namespace.
     * @return self
     */
    protected function bootstrap($namespace)
    {
        $a_ns = explode('\\', $namespace);
        for ($ii = 0, $jj = count($a_ns); $ii < $jj; $ii++) {
            $s_ns = $ii ? "{$s_ns}\\{$a_ns[$ii]}" : $a_ns[0];
            if (!isset($this->packages[$s_ns]) || $this->packages[$s_ns][1]) {
                continue;
            }
            if (!is_dir($this->packages[$s_ns][0]) || !is_readable($this->packages[$s_ns][0])) {
                return false;
            }
            $p_bs = "{$this->packages[$s_ns][0]}/@bootstrap.php";
            if (is_file($p_bs) && is_readable($p_bs)) {
                require_once $p_bs;
            }
            $this->packages[$s_ns][1] = true;
        }
        return $this;
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
