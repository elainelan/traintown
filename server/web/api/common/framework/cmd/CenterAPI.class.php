<?php
/**
 * CenterAPI通讯处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CenterAPI通讯处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class CenterAPI
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
     * ......
     * )
     */
    public static function decrypt()
    {
        $data = HttpParam::post('data');
        if (empty($data)) {
            ResultParser::error(CErrorCode::PARAM_ERROR);
        }
        self::$data = Sign::decryptData($data);
        if (self::$data === false) {
            ResultParser::error(CErrorCode::API_DATA_ERROR);
        }
    }
}

