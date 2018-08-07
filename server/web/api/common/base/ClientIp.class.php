<?php
/**
 * 请求IP处理
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 请求IP处理类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class ClientIp
{

    /**
     * 客户端IP
     *
     * @var string
     */
    private static $client_ip;

    /**
     * 获取客户端访问IP
     */
    public static function get()
    {
        if (!self::$client_ip) {
            self::$client_ip = HttpParam::server('REMOTE_ADDR');
        }
        return self::$client_ip;
    }

}

