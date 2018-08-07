<?php
/**
 * AdminPermissions-权限相关处理
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 权限相关逻辑处理类
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */
class AdminPermissions
{
    /**
     * 默认需要检查
     * 
     * @var boolean
     */
    public static $need_check = true;

    /**
     * 不需要判断权限的接口数组
     *
     * @var array
     */
    private static $except_uris = array(
        'test',         // 测试类
        'wechat_service_callback',//微信服务号回调
        'admin.login',  // 登录
        'admin.is_logined', // 是否登录
        'admin.logout', // 登出
        'admin.menu',   // 菜单
        'common',       // 获取通用信息接口
        'capi',         // GS调用Center接口
        
    );
    /**
     * 检查权限是否允许
     */
    public static function check()
    {
        if (self::_isNeedCheck()) {
            if (!AdminLogin::$admin_uinfo) {
                $session_id = HttpParam::cookie(AdminSession::SESSION_COOKIE_KEY);
                if (!empty($session_id)) {
                    ResultParser::error(CErrorCode::ADMIN_USER_LOGINED_EXPIRE);
                }
                ResultParser::error(CErrorCode::ADMIN_USER_NOT_LOGINED);
            }
            $check_uris = Api::getUri();
            $forbidden = true;
            foreach ($check_uris as $check_uri) {
                if (isset(AdminLogin::$admin_uinfo['pri'][$check_uri]) || isset(AdminLogin::$admin_uinfo['pri_ext'][$check_uri])) {
                    $forbidden = false;
                    break;
                }
            }
            if ($forbidden) {
                ResultParser::error(CErrorCode::PERMISSION_FORBIDDEN);
            }
        }
    }

    /**
     * 是否需要检查权限
     *
     * @return boolean
     */
    private static function _isNeedCheck()
    {
        if (!self::$need_check) {
            // 忽略所有检查
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

    /**
     * 功能扩展权限检查
     *
     * @param string $action
     *        action
     * @return boolean true/false
     */
    public static function hasActionPermission($action)
    {
        $check_uris = Api::getUri();
        $check_uris[2] = Api::getClass() . '.' . Api::getMethod() . '.' . $action;
        
        $has_action_permission = false;
        foreach ($check_uris as $check_uri) {
            if (isset(AdminLogin::$admin_uinfo['pri'][$check_uri]) || isset(AdminLogin::$admin_uinfo['pri_ext'][$check_uri])) {
                $has_action_permission = true;
                break;
            }
        }
        
        return $has_action_permission;
    }
}