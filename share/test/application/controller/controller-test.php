<?php
/**
 * Defines the test case for Tox\Application\Controller\Controller.
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

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../../src/core/assembly.php';
require_once __DIR__ . '/../../../../src/application/icontroller.php';
require_once __DIR__ . '/../../../../src/application/controller/controller.php';

require_once __DIR__ . '/../../../../src/application/application.php';

use Tox\Application;

/**
 * Tests Tox\Application\Controller\Controller.
 *
 * @internal
 *
 * @package tox.application.controller
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ControllerTest extends PHPUnit_Framework_TestCase
{
    public function testApplicationContextAttachedOnConstructing()
    {
        $o_app = $this->getMockBuilder('Tox\\Application\\Application')
            ->setMethods(array('getInput', 'getOutput', 'getConfig'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $o_app->expects($this->once())->method('getInput');
        $o_app->expects($this->once())->method('getOutput');
        $o_app->expects($this->once())->method('getConfig');
        $o_ctrl = $this->getMockForAbstractClass('Tox\\Application\\Controller\\Controller', array($o_app));
    }
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
