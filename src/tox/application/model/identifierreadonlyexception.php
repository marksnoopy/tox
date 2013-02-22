<?php
/**
 * 发生在 set 对象id 时
 * 
 * @package    Tox\Application\Model
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Application\Model;

use Tox;

class IdentifierReadOnlyException extends Tox\Exception {

    const CODE = 0x80000006;

	protected static $TEMPLATE = 'Identifier ReadOnly .';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: