<?php
/**
 * Defines the test case for Tox\Application\Application.
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
 * @copyright © 2012-2013 PHP-Tox.org
 * @license   GNU General Public License, version 3
 */

namespace Tox\Application;

use PHPUnit_Framework_TestCase;

require_once __DIR__ . '/../../../src/core/assembly.php';
require_once __DIR__ . '/../../../src/application/application.php';

require_once __DIR__ . '/../../../src/core/exception.php';
require_once __DIR__ . '/../../../src/application/@exception/invalidconfiguredfallbacktype.php';
require_once __DIR__ . '/../../../src/application/@exception/invalidconfiguredinputtype.php';
require_once __DIR__ . '/../../../src/application/@exception/invalidconfiguredoutputtype.php';
require_once __DIR__ . '/../../../src/application/@exception/invalidconfiguredroutertype.php';
require_once __DIR__ . '/../../../src/application/@exception/multipleapplicationruntime.php';

require_once __DIR__ . '/../../../src/application/iinput.php';
require_once __DIR__ . '/../../../src/application/ioutputtask.php';
require_once __DIR__ . '/../../../src/application/ioutput.php';
require_once __DIR__ . '/../../../src/application/iview.php';
require_once __DIR__ . '/../../../src/application/ifallback.php';
require_once __DIR__ . '/../../../src/application/iconfiguration.php';
require_once __DIR__ . '/../../../src/application/irouter.php';
require_once __DIR__ . '/../../../src/application/itoken.php';
require_once __DIR__ . '/../../../src/application/icontroller.php';

/**
 * Tests Tox\Application\Application.
 *
 * @internal
 *
 * @package tox.application
 * @author  Snakevil Zen <zsnakevil@gmail.com>
 */
class ApplicationTest extends PHPUnit_Framework_TestCase
{
    /**
     * Stores mocked instance of the configuration.
     *
     * @var IConfiguration
     */
    protected $ocfg;

    /**
     * Stores the mocking class name of the configuration.
     *
     * @var string
     */
    protected $ccfg;

    /**
     * Stores mocked instance of the router.
     *
     * @var IRouter
     */
    protected $orouter;

    /**
     * Stores the mocking class name of the router.
     *
     * @var string
     */
    protected $crouter;

    /**
     * Stores mocked instance of the dispatching token.
     *
     * @var IToken
     */
    protected $otoken;

    /**
     * Stores the mocking class name of the dispatching token.
     *
     * @var string
     */
    protected $ctoken;

    /**
     * Stores mocked instance of the falling back view.
     *
     * @var IFallback
     */
    protected $ofb;

    /**
     * Stores the mocking class name of the falling back view.
     *
     * @var string
     */
    protected $cfb;

    /**
     * Stores mocked instance of the input.
     *
     * @var IInput
     */
    protected $oin;

    /**
     * Stores the mocking class name of the input.
     *
     * @var string
     */
    protected $cin;

    /**
     * Stores mocked instance of the output.
     *
     * @var IOutput
     */
    protected $oout;

    /**
     * Stores the mocking class name of the output.
     *
     * @var string
     */
    protected $cout;

    /**
     * Stores mocked instance of the controller.
     *
     * @var IController
     */
    protected $octrl;

    /**
     * Stores the mocking class name of the controller.
     *
     * @var string
     */
    protected $cctrl;

    protected function setUp()
    {
        $this->ccfg = 'c' . md5(microtime());
        $this->ocfg = $this->getMock('Tox\\Application\\IConfiguration', array(), array(), $this->ccfg);
        $this->crouter = 'c' . md5(microtime());
        $this->orouter = $this->getMock('Tox\\Application\\IRouter', array(), array(), $this->crouter);
        $this->ctoken = 'c' . md5(microtime());
        $this->otoken = $this->getMock('Tox\\Application\\IToken', array(), array(), $this->ctoken);
        $this->cfb = 'c' . md5(microtime());
        $this->ofb = $this->getMock('Tox\\Application\\IFallback', array(), array(), $this->cfb);
        $this->cin = 'c' . md5(microtime());
        $this->oin = $this->getMock('Tox\\Application\\IInput', array(), array(), $this->cin);
        $this->cout = 'c' . md5(microtime());
        $this->oout = $this->getMock('Tox\\Application\\IOutput', array(), array(), $this->cout);
        $this->cctrl = 'c' . md5(microtime());
        $this->octrl = $this->getMock('Tox\\Application\\IController', array(), array(), $this->cctrl);
    }

    public function testRunningWithDefaults()
    {
        $this->oin->expects($this->once())->method('recruit')
            ->with($this->equalTo($this->otoken));
        $this->orouter->expects($this->once())->method('analyse')
            ->with($this->equalTo($this->oin))
            ->will($this->returnValue($this->otoken));
        $this->octrl->expects($this->once())->method('act');
        $this->ocfg->expects($this->atLeastOnce())->method('offsetExists')
            ->will($this->returnValue(false));
        $o_app = $this->getMockBuilder('Tox\\Application\\Application')
            ->setMethods(
                array('getInstance', 'dispatch', 'getDefaultConfiguration', 'getDefaultRouter', 'getDefaultFallback')
            )->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $o_app->staticExpects($this->once())->method('getInstance')
            ->will($this->returnValue($o_app));
        $o_app->expects($this->once())->method('getDefaultConfiguration')
            ->will($this->returnValue($this->ocfg));
        $o_app->expects($this->once())->method('getDefaultRouter')
            ->will($this->returnValue($this->orouter));
        $o_app->expects($this->once())->method('getDefaultFallback');
        $o_app->expects($this->once())->method('getDefaultInput')
            ->will($this->returnValue($this->oin));
        $o_app->expects($this->once())->method('init');
        $o_app->expects($this->once())->method('dispatch')
            ->with($this->equalTo($this->otoken))
            ->will($this->returnValue($this->octrl));
        $o_app::run();
    }

    /**
     * @depends testRunningWithDefaults
     */
    public function testRunningWithParameters()
    {
        $this->orouter->expects($this->once())->method('analyse')
            ->will($this->returnValue($this->otoken));
        $this->octrl->expects($this->once())->method('act');
        $this->ocfg->expects($this->atLeastOnce())->method('offsetExists')
            ->will($this->returnValue(false));
        $o_app = $this->getMockBuilder('Tox\\Application\\Application')
            ->setMethods(
                array('getInstance', 'dispatch', 'getDefaultConfiguration', 'getDefaultRouter', 'getDefaultFallback')
            )->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $o_app->staticExpects($this->once())->method('getInstance')
            ->will($this->returnValue($o_app));
        $o_app->expects($this->never())->method('getDefaultConfiguration');
        $o_app->expects($this->never())->method('getDefaultRouter');
        $o_app->expects($this->never())->method('getDefaultConfiguration');
        $o_app->expects($this->once())->method('getDefaultInput')
            ->will($this->returnValue($this->oin));
        $o_app->expects($this->once())->method('dispatch')
            ->will($this->returnValue($this->octrl));
        $o_app::run($this->ocfg, $this->orouter, $this->ofb);
    }

    // TODO how to test further and deeper?
}

// vi:ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120
