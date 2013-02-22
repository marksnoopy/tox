<?php
/**
 * 发生在非法 Where 子句时
 * 
 * @package    Tox\Application\Dao
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Application\Dao;

use Tox;

class IllegalWhereClauseException extends Tox\Exception {

    const CODE = 0x80000008;

	protected static $TEMPLATE = 'Illegal Where Clause Exception  \'%where$s\'.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: