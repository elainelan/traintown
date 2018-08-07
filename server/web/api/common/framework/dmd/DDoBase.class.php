<?php
/**
 * 基础操作类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/dmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 基础操作类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/dmd
 */
class DDoBase
{

    /**
     * 数据解密方法
     */
    protected function decrypt()
    {
        // sign验证
        if (!Sign::validSign(HttpParam::get(), API_DB_SIGN_KEY)) {
            ResultParser::error(DErrorCode::API_SIGN_ERROR);
        }
        // 数据解密
        DBAPI::decrypt();
    }

}

