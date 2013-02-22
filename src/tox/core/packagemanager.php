<?php
/**
 * Represents as a runtime packages manager.
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
 * @package    Tox
 * @subpackage Tox\Core
 * @author     Snakevil Zen <zsnakevil@gmail.com>
 * @copyright  © 2012 szen.in
 * @license    http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Core;

use Exception;

use Tox;

class PackageManager extends Tox\Assembly
{
    protected $conjecturalPackages;

    protected $packages;

    protected $phars;

    protected $roots;

    protected function addPackage($package, $path, $from = '')
    {
        if (!$package)
        {
            return $this->addRoot($path, $from);
        }
        if ($this->hasPackage($package))
        {
            if ($from)
            {
                return $this;
            }
            if (!$this->isConjectural($package))
            {
                trigger_error("Tox: package '$package' already registered.", E_USER_ERROR);
            }
            $this->dropPackage($package);
        }
        if (is_file($path))
        {
            try
            {
                Phar::loadPhar($path, $package);
                $this->phars[$package] = $path;
                $this->packages[$package] = "phar://$package";
            }
            catch (Exception $ex)
            {
                trigger_error("Tox: invalid Phar '$path'.", E_USER_ERROR);
            }
        }
        else
        {
            $this->packages[$package] = $path;
        }
        if ($from)
        {
            $this->conjecturalPackages[$package] = $from;
        }
        else
        {
            $this->bootstrap($package)->scanSubPackages($package);
        }
        return $this->findParentPackage($package);
    }

    protected function addRoot($path, $from)
    {
        if (!in_array($path, $this->roots))
        {
            $this->roots[$from] = $path;
        }
        return $this;
    }

    protected function bootstrap($package)
    {
        if (array_key_exists($package, $this->phars))
        {
            require_once($this->packages[$package] . '/');
        }
        else
        {
            $p_bs = $this->packages[$package] . '/@bootstrap.php';
            if (is_file($p_bs))
            {
                if (!is_readable($p_bs))
                {
                    trigger_error("Tox: package '$package' bootstrap access denied.", E_USER_ERROR);
                }
                require_once($this->packages[$package] . '/@bootstrap.php');
            }
        }
        return $this;
    }

    protected function compactPath($path)
    {
        if (!strlen($path))
        {
            return '';
        }
        if (preg_match('@^[a-z]+://@i', $path))
        {
            trigger_error("Tox: illegal remote URL '$path'.", E_USER_ERROR);
        }
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $a_old = explode(DIRECTORY_SEPARATOR, $path);
        switch ($a_old[0])
        {
            case '.':
                array_splice($a_old, 0, 1, explode(DIRECTORY_SEPARATOR, getcwd()));
            case '':
                break;
            case 'a:':
            case 'b:':
            case 'c:':
            case 'd:':
            case 'e:':
            case 'f:':
            case 'g:':
            case 'h:':
            case 'i:':
            case 'j:':
            case 'k:':
            case 'l:':
            case 'm:':
            case 'n:':
            case 'o:':
            case 'p:':
            case 'q:':
            case 'r:':
            case 's:':
            case 't:':
            case 'u:':
            case 'v:':
            case 'w:':
            case 'x:':
            case 'y:':
            case 'z:':
                if ('\\' == DIRECTORY_SEPARATOR)
                {
                    break;
                }
            default:
                array_splice($a_old, 0, 0, explode(DIRECTORY_SEPARATOR, getcwd()));
        }
        $i_old = count($a_old);
        $a_new = array();
        $i_new = 0;
        for ($ii = 0; $ii < $i_old; $ii++)
        {
            $s_node = array_shift($a_old);
            switch ($s_node)
            {
                case '':
                    if (!$ii)
                    {
                        $a_new[] = '';
                        $i_new++;
                    }
                case '.':
                    break;
                case '..':
                    if (1 < $i_new)
                    {
                        array_pop($a_new);
                        $i_new--;
                    }
                    break;
                default:
                    $a_new[] = $s_node;
                    $i_new++;
            }
        }
        return implode('/', $a_new);
    }

    protected function confirmPackage($package)
    {
        if (!$this->isConjectural($package))
        {
            return $this;
        }
        unset($this->conjecturalPackages[$package]);
        return $this->bootstrap($package);
    }

    public function __construct()
    {
        $this->conjecturalPackages =
        $this->packages =
        $this->phars =
        $this->roots = array();
    }

    protected function dropPackage($package)
    {
        if (!$this->hasPackage($package))
        {
            return $this;
        }
        foreach ($this->conjecturalPackages as $s_pack => $s_from)
        {
            if ($package == $s_from)
            {
                $this->dropPackage($s_pack);
            }
        }
        unset($this->conjecturalPackages[$package],
            $this->packages[$package],
            $this->phars[$package],
            $this->roots[$package]
        );
        return $this;
    }

    protected function findParentPackage($package)
    {
        $a_nnodes = array_reverse(explode('.', $package));
        $p_dir = $this->getPath($package);
        $a_bnodes = array_reverse(explode('.', basename($p_dir)));
        if (is_file($p_dir) && 'phar' == $a_bnodes[0])
        {
            array_shift($a_bnodes);
        }
        $i_bnodes = count($a_bnodes);
        if (array_slice($a_nnodes, 0, $i_bnodes) != $a_bnodes)
        {
            return $this;
        }
        $a_nnodes = array_reverse(array_slice($a_nnodes, $i_bnodes));
        return $this->addPackage(implode('.', $a_nnodes), dirname($p_dir), $package);
    }

    protected function getPath($package)
    {
        return array_key_exists($package, $this->phars) ? $this->phars[$package] : $this->packages[$package];
    }

    protected function hasPackage($package)
    {
        return array_key_exists($package, $this->packages);
    }

    protected function isConjectural($package)
    {
        if (!$this->hasPackage($package))
        {
            return FALSE;
        }
        return array_key_exists($package, $this->conjecturalPackages);
    }

    public function locateClass($class)
    {
        $a_nodes = explode('\\', strtolower($class));
        $i_nodes = count($a_nodes);
        for ($ii = $i_nodes - 1; 0 < $ii; $ii--)
        {
            $s_pack = implode('.', array_slice($a_nodes, 0, $ii));
            if ($this->hasPackage($s_pack))
            {
                $p_class = $this->packages[$s_pack] . '/'. implode('/', array_slice($a_nodes, $ii));
                $s_pack = implode('.', array_slice($a_nodes, 0, $i_nodes - 1));
                $p_test = $p_class . '.php';
                if (!is_file($p_test))
                {
                    $s_pack .= '.' . $a_nodes[$i_nodes - 1];
                    $p_test = $p_class . '/' . $a_nodes[$i_nodes - 1] . '.php';
                }
                if (is_file($p_test))
                {
                    if ($this->isConjectural($s_pack))
                    {
                        $this->confirmPackage($s_pack);
                    }
                    if (!$this->hasPackage($s_pack))
                    {
                        $this->addPackage($s_pack, dirname($p_test));
                    }
                }
                return $p_test;
            }
        }
        trigger_error('TODO: locate from roots.', E_USER_ERROR);
    }

    public function registerPackage($package, $path)
    {
        settype($package, 'string');
        settype($path, 'string');
        $path = $this->compactPath($path);
        if (is_file($path))
        {
            if (in_array($path, $this->phars))
            {
                trigger_error("Tox: Phar '$path' repetitiously used.", E_USER_WARNING);
            }
            else if (!is_readable($path))
            {
                trigger_error("Tox: Phar '$path' access denied.", E_USER_ERROR);
            }
        }
        else if (is_dir($path))
        {
            if (in_array($path, $this->packages))
            {
                trigger_error("Tox: directory '$path' repetitiously used.", E_USER_WARNING);
            }
            else if (!is_readable($path))
            {
                trigger_error("Tox: directory '$path' access denied.", E_USER_ERROR);
            }
        }
        else
        {
            trigger_error("Tox: unknown path '$path'.", E_USER_ERROR);
        }
        return $this->addPackage($package, $path);
    }

    protected function scanSubPackages($package)
    {
        if (array_key_exists($package, $this->phars))
        {
            return $this;
        }
        $a_files = scandir($this->packages[$package]);
        $s_re = '@^\w+(\.\w+)*$@';
        for ($ii = 0, $jj = count($a_files); $ii < $jj; $ii++)
        {
            if (!preg_match($s_re, $a_files[$ii]))
            {
                continue;
            }
            $p_file = $this->packages[$package] . DIRECTORY_SEPARATOR . $a_files[$ii];
            if (!is_readable($p_file))
            {
                continue;
            }
            if (is_dir($p_file))
            {
                $s_suffix = $a_files[$ii];
            }
            else if (is_file($p_file) && '.phar' == substr($a_files[$ii], -5))
            {
                $s_suffix = substr($a_files[$ii], 0, -5);
            }
            else
            {
                continue;
            }
            $this->addPackage($package . '.' . $s_suffix, $p_file, $package);
        }
        return $this;
    }
}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
