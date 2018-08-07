<?php
/**
 * 通用信息操作
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * 通用信息操作类
 *
 * @author dragonets
 * @package do
 * @subpackage cmd
 */
class common
{

    /**
     * 获取平台列表
     * 本平台用户，只能获取本平台信息
     * 跨平台用户，可以获取跨平台信息[platid=0]及所有平台信息[platid=null]
     *
     */
    public function platforms()
    {
        // 平台ID
        $platid = HttpParam::request('platid');
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            $platid = $admin_uinfo['platid'];
        }
        
        $db_admin_platforms = CDbuser::getInstanceTable('AdminPlatforms');
        // $db_admin_platforms = new AdminPlatforms(); // for dev
        

        $db_data = $db_admin_platforms->getCommonInfos($platid);
        
        ResultParser::succ($db_data);
    }

    /**
     * 获取用户信息（通用）
     * 本平台用户，只能获取本平台用户信息
     * 跨平台用户，可以获取跨平台用户信息[platid=0]及所有平台用户信息[platid=null]
     */
    public function admin_users()
    {
        // 平台ID
        $platid = HttpParam::request('platid');
        $admin_uinfo = AdminLogin::$admin_uinfo;
        if ($admin_uinfo['platid']) {
            $platid = $admin_uinfo['platid'];
        }
        
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        //$db_admin_users = new AdminPlatforms(); // for dev
        

        $db_data = $db_admin_users->getCommonInfos($platid);
        
        ResultParser::succ($db_data);
    }

    /**
     * 随机生成md5码
     */
    public function get_md5()
    {
        $num = (int)HttpParam::request('num');
        if ($num <= 0) {
            $num = 1;
        }
        
        $md5_array = array();
        for ($i = 1; $i <= $num; --$num) {
            $md5_array[] = md5(HttpParam::server('REMOTE_ADDR') . microtime(1) . rand(10000, 99999));
        }
        
        ResultParser::succ($md5_array);
    }
}