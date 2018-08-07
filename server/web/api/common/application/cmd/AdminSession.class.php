<?php
/**
 * AdminSession-登录session处理
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AdminSession-登录session处理类
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */
class AdminSession
{

    /**
     * session_id存在cookie中的key
     *
     * @var string
     */
    const SESSION_COOKIE_KEY = 's';

    /**
     * 设置session
     *
     * @param string $session_id
     *        session_id
     * @param array $session
     *        session
     */
    public static function set($session_id, $session)
    {
        return CDbuser::getInstanceMemcache()->set($session_id, $session, CDbuser::CACHE_TIME);
    }

    /**
     * 获取session
     *
     * @param string $session_id
     *        session_id
     */
    public static function get($session_id)
    {
        return CDbuser::getInstanceMemcache()->get($session_id);
    }

    /**
     * 删除session
     *
     * @param string $session_id
     *        session_id
     */
    public static function del($session_id)
    {
        return CDbuser::getInstanceMemcache()->delete($session_id);
    }

    /**
     * 生成session_id
     *
     * @return string session_id
     */
    public static function createSessionId()
    {
        list($u, $s) = explode(' ', microtime());
        $rnd = rand(100000, 999999);
        return md5(ClientIp::get() . $u . $s . $rnd);
    }

}
