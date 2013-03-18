<?php
/**
 * 发生在未知的应用现状时
 * 
 * @package    Tox\Application\Dao
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Application\Router;

use Tox;

class UnknownApplicationSituationException extends Tox\Exception {

    const CODE = 0x8000000C;

	protected static $TEMPLATE = 'Unknown Application Situation Exception \'%input$s\'.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: