<?php
/**
 * 发生在空数据源时
 *
 * @package    Tox\Data\Pdo
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Data\Pdo;

use Tox\Core;

class EmptyDataSourceException extends Core\Exception {

    const CODE = 0x8000000E;

	const MESSAGE = 'Empty Data Source Exception \'%source$s\'.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet:
