<?php
/**
 * AdminLogin-登录相关处理
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 登录相关逻辑封装类
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */
class AdminLogin
{
    /**
     * 默认需要登录检查（true）
     * 
     * @var boolean
     */
    public static $need_check = true;

    /**
     * 不需要检查登录态的接口
     *
     * @var array
     */
    private static $except_uris = array(
        'test',             // 测试类
        'wechat_service_callback',//微信服务号回调
        'admin.login',      // 登录接口
        'capi',                 // GS调用Center接口
    );
    /**
     * 登录状态信息
     *
     * @var array
     */
    public static $admin_uinfo;

    /**
     * 登录状态检查
     */
    public static function check()
    {
        if (self::_isNeedCheck()) {
            if (empty(self::$admin_uinfo)) {
                $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
                $admin_uinfo = $db_admin_users->isLogined();
                
                if ($admin_uinfo === false) {
                    $session_id = HttpParam::cookie(AdminSession::SESSION_COOKIE_KEY);
                    if (!empty($session_id)) {
                        ResultParser::error(CErrorCode::ADMIN_USER_LOGINED_EXPIRE);
                    }
                    ResultParser::error(CErrorCode::ADMIN_USER_NOT_LOGINED);
                }
                else if ($admin_uinfo['force_pwd']) {
                    // 修改密码接口，账号是首次登录修改密码状态，不需要报错，需要允许修改密码
                    $uri = Api::getClass() . '.' . Api::getMethod();
                    if ($uri != 'admin.modpwd_self') {
                        ResultParser::error(CErrorCode::ADMIN_USER_FORCE_PWD);
                    }
                }
                self::$admin_uinfo = $admin_uinfo;
            }
        }
    }

    /**
     * 是否需要检查登录态
     *
     * @param string $class
     *        接口类名
     * @param string $method
     *        接口类方法名
     * @return boolean
     */
    private static function _isNeedCheck()
    {
        if (!self::$need_check) {
            // 所有检查忽略
            return false;
        }
        if (self::$except_uris) {
            $check_uris = Api::getUri();
            foreach (self::$except_uris as $except_uri) {
                if (in_array($except_uri, $check_uris)) {
                    return false;
                }
            }
        }
        return true;
    }
}