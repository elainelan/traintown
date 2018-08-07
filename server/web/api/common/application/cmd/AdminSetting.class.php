<?php
/**
 * AdminSetting-后台配置相关处理
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 登录后，后台配置相关封装类
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */
class AdminSetting
{

    /**
     * 配置信息
     *
     * @var array
     */
    public static $admin_settings = array();

    /**
     * 配置获取
     */
    public static function get()
    {
        $db_admin_settings = CDbuser::getInstanceTable("AdminSettings");
        $db_admin_settings = new AdminSettings(); // for dev
        
        $id = 1;
        $res = $db_admin_settings->getWithCache($id);
        if ($res[$id]['settings']) {
            self::$admin_settings = json_decode($res[$id]['settings'], true);
        }
    }

}