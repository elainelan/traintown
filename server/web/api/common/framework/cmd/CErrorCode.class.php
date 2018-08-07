<?php
/**
 * 错误代码处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 错误代码类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class CErrorCode extends ErrorCode
{
    // 自定义错误，从100000开始
    

    // 后台登录失败
    const ADMIN_USER_LOGIN_FAIL     = 100001;
    // 账号未登录
    const ADMIN_USER_NOT_LOGINED    = 100002;
    // 账号需要强制修改密码
    const ADMIN_USER_FORCE_PWD      = 100003;
    // 登录需要安全码
    const ADMIN_LOGIN_NEED_SAFEKEY  = 100004;
    // 登出失败
    const ADMIN_LOGOUT_FAIL         = 100005;
    // 账号登录过期
    const ADMIN_USER_LOGINED_EXPIRE = 100006;
    
    // 账号已存在
    const ADMIN_USER_EXIST          = 100008;
    // 创建账号失败
    const ADMIN_USER_ADDED_FAIL     = 100009;
    
    // 密码规则验证失败
    const ADMIN_PWD_VALID_FAIL      = 100100;
    // 原密码错误
    const ADMIN_OLDPWD_ERROR        = 100101;
    // 安全码规则验证失败
    const ADMIN_SAFEKEY_VALID_FAIL  = 100102;
    // 原安全码错误
    const ADMIN_OLDSAFEKEY_ERROR    = 100103;
    
    // 平台ID错误
    const PLATFORM_ID_ERROR         = 100200;
    // 没有对应的平台信息
    const PLATFORM_GET_NULL         = 100201;
    // 平台已存在
    const PLATFORM_EXIST            = 100202;
    // 平台信息删除失败
    const PLATFORM_DEL_ERROR        = 100203;
    // 平台信息修改失败
    const PLATFORM_MOD_ERROR        = 100204;
    
    // 该IP已被其他平台管理
    const IP_EXIST_IN_OTHERS        = 100400;
    
    // 服务器SID已存在
    const SID_EXIST                 = 100500;
    // 服务器信息删除失败
    const SID_DEL_ERROR             = 100501;
    

}