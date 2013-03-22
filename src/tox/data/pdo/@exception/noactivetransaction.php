<?php
/**
 * 发生在没有活动的事务时
 *
 * @package    Tox\Data\Pdo
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Data\Pdo;

use Tox\Core;

class NoActiveTransactionException extends Core\Exception {

    const CODE = 0x8000000F;

	const MESSAGE = 'No Active Transaction Exception ';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet:
