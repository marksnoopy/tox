<?php
/**
 * 发生在非法预期的条件现场异常时
 * 
 * @package    Tox\Application\Dao
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Application\Dao;

use Tox;

class IllegalExpectedConditionFieldException extends Tox\Exception {

    const CODE = 0x8000000A;

	protected static $TEMPLATE = 'Illegal Expected Condition Field Exception  \'%clause$s\' \'%field$s\'.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: