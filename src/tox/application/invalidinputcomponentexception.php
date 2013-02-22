<?php
/**
 * 发生在无效的输入组件时
 * 
 * @package    Tox\Application
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Application;

use Tox;

class InvalidInputComponentException extends Tox\Exception {

    const CODE = 0x80000004;

	protected static $TEMPLATE = 'Invalid Input Component Exception \'%input$s\' .';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: