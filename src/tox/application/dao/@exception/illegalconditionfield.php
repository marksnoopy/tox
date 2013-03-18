<?php
/**
 * 发生在非法条件现场异常时
 * 
 * @package    Tox\Application\Dao
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Application\Dao;

use Tox;

class IllegalConditionFieldException extends Tox\Exception {

    const CODE = 0x8000000B;

	protected static $TEMPLATE = 'Illegal Condition Field Exception \'%field$s\' \'%type$s\' \'%value$s\'.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: