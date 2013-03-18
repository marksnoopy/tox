<?php
/**
 * 发生在元素实体转换失败时
 * 
 * @package    Tox\Application\Model
 * @author     Redrum Xiang <xiangcy@ucweb.com>
 * @copyright  2012 (c) www.uc.cn
 */

namespace Tox\Application\Model;

use Tox;

class ElementEntityConversionFailedException extends Tox\Exception {

    const CODE = 0x80000005;

	protected static $TEMPLATE = 'Element Entity Conversion Failed .';

}

# vim:se ft=php ff=unix fenc=utf-8 tw=120 noet: