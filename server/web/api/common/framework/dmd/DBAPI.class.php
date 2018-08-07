<?php
/**
 * DBAPI通讯处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/dmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * DBAPI通讯处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/dmd
 */
class DBAPI
{

    /**
     * 解密数据数组
     *
     * @var array
     */
    public static $data;

    /**
     * 获取解密数据
     *
     * array(
     * platid
     * sid
     * server
     * cmds = array()
     * )
     */
    public static function decrypt()
    {
        $data = HttpParam::post('data');
        if (empty($data)) {
            ResultParser::error(DErrorCode::PARAM_ERROR);
        }
        self::$data = Sign::decryptData($data);
        if (self::$data === false) {
            ResultParser::error(DErrorCode::API_DATA_ERROR);
        }
    }
}

