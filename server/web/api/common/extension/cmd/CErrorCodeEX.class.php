<?php
/**
 * 错误代码处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 错误代码类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class CErrorCodeEX extends CErrorCode
{
    // 自定义错误，从X000000开始
    // 2000000: API_KEYS
    // 6000000: YLB

}