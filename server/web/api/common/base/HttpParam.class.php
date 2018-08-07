<?php
/**
 * 处理http请求参数
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * http请求参数解析类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class HttpParam
{

    /**
     * 获取$_GET数据
     *
     * @param string $param_name
     *        $_GET参数名
     */
    public static function get($param_name = null)
    {
        return self::_getHttpParam('GET', $param_name);
    }

    /**
     * 获取$_POST数据
     *
     * @param string $param_name
     *        $_POST参数名
     */
    public static function post($param_name = null)
    {
        return self::_getHttpParam('POST', $param_name);
    }

    /**
     * 获取$_REQUEST数据
     *
     * @param string $param_name
     *        $_REQUEST参数名
     */
    public static function request($param_name = null)
    {
        return self::_getHttpParam('REQUEST', $param_name);
    }

    /**
     * 获取$_COOKIE数据
     *
     * @param string $cookie_name
     *        $_COOKIE参数名
     */
    public static function cookie($cookie_name = null)
    {
        return self::_getHttpParam('COOKIE', $cookie_name);
    }

    /**
     * 获取$_SESSION数据
     *
     * @param string $session_name
     *        $_SESSION参数名
     */
    public static function session($session_name = null)
    {
        return self::_getHttpParam('SESSION', $session_name);
    }

    /**
     * 获取$_SERVER数据
     *
     * @param string $param_name
     *        $_SERVER参数名
     */
    public static function server($param_name = null)
    {
        return self::_getHttpParam('SERVER', $param_name);
    }

    /**
     * 获取http参数值
     *
     * @param string $type
     *        数据类型
     * @param string $name
     *        参数名称
     * @return string|array()
     */
    private static function _getHttpParam($type, $name)
    {
        switch ($type) {
            case 'GET':
                $var = & $_GET;
                break;
            case 'POST':
                $var = & $_POST;
                break;
            case 'REQUEST':
                $var = & $_REQUEST;
                break;
            case 'COOKIE':
                $var = & $_COOKIE;
                break;
            case 'SESSION':
                $var = & $_SESSION;
                break;
            case 'SERVER':
                $var = & $_SERVER;
                break;
        }
        if ($name !== null) {
            return isset($var[$name]) ? $var[$name] : null;
        }
        else {
            return $var;
        }
    }

    /**
     * 获取php://input数据
     */
    public static function getInput()
    {
        return file_get_contents("php://input");
    }
}