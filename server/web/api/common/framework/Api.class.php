<?php
/**
 * 接口请求Api处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 接口请求Api处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class Api
{

    /**
     * 接口类名
     *
     * @var string
     */
    private static $class;

    /**
     * 接口方法名
     *
     * @var string
     */
    private static $method;

    /**
     * 接口action名
     *
     * @var string
     */
    private static $action;

    /**
     * 接口URI数组
     *
     * @var array
     */
    private static $uri;

    /**
     * 操作记录operation_id
     *
     * @var string
     */
    private static $oper_id;

    /**
     * 初始化
     *
     * @param string $class
     *        接口类
     * @param string $method
     *        接口方法
     * @param string $action
     *        接口action
     */
    public static function init($class, $method, $action)
    {
        self::$class = $class;
        self::$method = $method;
        self::$action = $action;
    }

    /**
     * 初始化操作记录ID
     *
     * @param string $oper_id
     *        操作ID
     */
    public static function setOperId($oper_id)
    {
        self::$oper_id = $oper_id;
    }

    /**
     * 获取接口类名
     *
     * @return string
     */
    public static function getClass()
    {
        return self::$class;
    }

    /**
     * 获取接口方法
     *
     * @return string
     */
    public static function getMethod()
    {
        return self::$method;
    }

    /**
     * 获取接口action
     *
     * @return string
     */
    public static function getAction()
    {
        return self::$action;
    }

    /**
     * 生成Uri
     *
     * @return array
     */
    public static function getUri()
    {
        if (empty(self::$uri)) {
            self::$uri = array(
                self::$class,
                self::$class.'.'.self::$method,
            );
            if (self::$action) {
                self::$uri[] = self::$class . '.' . self::$method . '.' . self::$action;
            }
        }
        return self::$uri;
    }

    /**
     * 获取接口action
     *
     * @return string
     */
    public static function getOperId()
    {
        return self::$oper_id;
    }
}

