<?php
/**
 * 发生在使用不支持的驱动程序列表时
 * 
 * @package    Tox\Data\Pdo
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Data\Pdo;

use Tox;

class UnsupportedDriverToListTablesException extends Tox\Exception {

    const CODE = 0x8000000D;

	protected static $TEMPLATE = 'Unsupported Driver To List Tables Exception \'%driver$s\'.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: