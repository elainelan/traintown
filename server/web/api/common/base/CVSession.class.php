<?php
/**
 * 封装session操作文件
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * session操作类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class CVSession
{

    /**
     * session默认过期时间10800，单位秒
     *
     * 如果使用setSessionConfig配置过过期时间，以配置后的过期时间为准
     *
     * @var int
     * @see $expire_time,setSessionConfig()
     */
    const EXPIRE_TIME = 10800;

    /**
     * 存储session建立时间的key
     *
     * @var string
     */
    const START_TIME_KEY = 'session_start_time';

    /**
     * session是否启动标志
     *
     * @var bool
     */
    private static $s_start = false;

    /**
     * 自定义配置：session过期时间，单位秒
     *
     * 如果没有配置(false)，以EXPIRE_TIME参数为准
     *
     * @var int false
     * @see EXPIRE_TIME,setSessionConfig()
     */
    private static $expire_time = false;

    /**
     * 自定义配置：session方式
     *
     * 0：默认，表示从建立session会话开始计算，多少秒后session失效。<br/>
     * 1：表示从最近一次session调用计算，多少秒后session失效
     *
     * @var int
     * @see $expire_time,EXPIRE_TIME,setSessionConfig()
     */
    private static $mode = 0;

    /**
     * 开启session会话
     *
     * @access protected
     * @return void
     */
    protected static function _checkSessionStarted()
    {
        if (self::$s_start === true) {
            if (self::$mode == 1) {
                $_SESSION[self::START_TIME_KEY] = time();
            }
            return;
        }
        $rs = session_start();
        if ($rs === false) {
            $rs = session_start();
        }
        if ($rs) {
            self::$s_start = true;
            if (!isset($_SESSION[self::START_TIME_KEY]) || self::$mode == 1) {
                $_SESSION[self::START_TIME_KEY] = time();
            }
        }
    }

    /**
     * 检查session是否过期
     *
     * @access protected
     * @return bool 过期true，未过期false
     */
    protected static function _checkExpire()
    {
        $expire_time = self::$expire_time !== false ? self::$expire_time : self::EXPIRE_TIME;
        if (time() - $_SESSION[self::START_TIME_KEY] > $expire_time) {
            session_destroy();
            self::$s_start = false;
            return true;
        }
        return false;
    }

    /**
     * 设置session值
     *
     * @param string $key
     *        session键值
     * @param mixed $val
     *        session值
     * @param int $force
     *        设置session之前，强制销毁之前的所有session，默认0，关闭
     * @return bool 成功返回true，失败返回false
     */
    public static function setSession($key, $val, $force = 0)
    {
        if ($force) {
            session_destroy();
            self::$s_start = false;
        }
        self::_checkSessionStarted();
        if (self::_checkExpire()) {
            return false;
        }
        $_SESSION['cv_' . $key] = $val;
        return true;
    }

    /**
     * 取得session值
     *
     * @param string $key
     *        session键值
     * @return mixed 成功返回session值，没有该键对应值或过期返回false
     */
    public static function getSession($key)
    {
        self::_checkSessionStarted();
        if (self::_checkExpire() || !isset($_SESSION['cv_' . $key])) {
            return false;
        }
        return $_SESSION['cv_' . $key];
    }

    /**
     * 销毁session会话
     *
     * @return void
     */
    public static function destroySession()
    {
        session_start();
        session_destroy();
    }

    /**
     * 配置session参数
     *
     * @param int|false $expire_time
     *        过期时间
     * @param int $mode
     *        session模式
     * @see $mode,$expire_time
     * @return void
     */
    public static function setSessionConfig($expire_time, $mode = 0)
    {
        self::$expire_time = $expire_time;
        self::$mode = $mode;
    }
}