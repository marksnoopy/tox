<?php
/**
 * 发生在预期的条件字段缺失时
 * 
 * @package    Tox\Application\Dao
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Application\Dao;

use Tox;

class ExpectedConditionFieldMissingException extends Tox\Exception {

    const CODE = 0x80000009;

	protected static $TEMPLATE = 'Expected Condition Field Missing Exception  \'%clause$s\' \'%field$s\'.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: