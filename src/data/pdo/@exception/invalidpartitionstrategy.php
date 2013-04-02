<?php
/**
 * 发生在无效的分区策略时
 *
 * @package    Tox\Data\Pdo
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Data\Pdo;

use Tox\Core;

class InvalidPartitionStrategyException extends Core\Exception {

    const CODE = 0x8000000F;

	const MESSAGE = 'Invalid Partition Strategy Exception \'%strategy$s\'.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet:
