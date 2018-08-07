<?php
/**
 * GSAPI通讯处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * GSAPI通讯处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */
class GSAPI
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
            ResultParser::error(GErrorCode::PARAM_ERROR);
        }
        self::$data = Sign::decryptData($data);
        if (self::$data === false) {
            ResultParser::error(GErrorCode::API_DATA_ERROR);
        }
    }
}

