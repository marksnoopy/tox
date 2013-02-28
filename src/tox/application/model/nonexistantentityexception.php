<?php
/**
 * Raised on setting up a non-existatnt entity.
 *
 * @package   Tox\Application
 * @author    Snakevil Zen <zsnakevil@gmail.com>
 * @copyright © 2012 szen.in
 * @license   http://www.gnu.org/licenses/gpl.html
 */

namespace Tox\Application\Model;

use Tox;

class NonExistantEntityException extends Tox\Exception {

    const CODE = 0x80000008;

    protected static $TEMPLATE = 'Entity \'%id$s\' of \'%type$s\' does not exist.';

}

// vi:se ft=php fenc=utf-8 ff=unix ts=4 sts=4 et sw=4 fen fdm=indent fdl=1 tw=120:
