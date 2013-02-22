<?php
/**
 * 发生在 set 对象id 时
 * 
 * @package    Tox\Application\Dao
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Application\Dao;

use Tox;

class DataDomainRebindingException extends Tox\Exception {

    const CODE = 0x80000007;

	protected static $TEMPLATE = 'Data Domain Rebinding  \'%domain$s\'.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: