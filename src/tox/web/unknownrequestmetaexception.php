<?php
/**
 * 发生在未知的请求时
 * 
 * @package    Tox\Web
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Web;

use Tox;

class UnknownRequestMetaException extends Tox\Exception {

    const CODE = 0x80000003;

	protected static $TEMPLATE = 'unknown request \'%field$s\' exception.';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: