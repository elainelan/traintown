<?php
/**
 * 微信服务号回调处理（通过中转服务器中转回来的请求）
 *
 * @author whx
 * @package do
 * @subpackage cmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * 微信服务号回调处理
 *
 * @author whx
 * @package do
 * @subpackage cmd
 */
class wechat_service_callback
{

    /**
     * 处理微信回调信息
     * 事件类型说明 
     *   1：绑定微信
     *   2：....
     */
    public function index()
    {
        if (AdminSetting::$admin_settings['weixin_service_callback_ip'] != ClientIp::get()) {
            ResultParser::error('IP FORBID');
        }
        // 回调事件
        $eventkeyarr = explode(',', HttpParam::request('EventKey'));
        if (!isset($eventkeyarr[1])) {
            ResultParser::error('PARAM "EventKey" MISSING');
        }
        switch ($eventkeyarr[1]) {
            case '1':// 绑定微信
                $this->_bind();
                break;
        }
    }
    
    /**
     * 绑定微信账号处理
     */
    private function _bind()
    {
        $eventkeyarr = explode(',', HttpParam::request('EventKey'));
        if (!isset($eventkeyarr[1])) {
            ResultParser::error('PARAM "EventKey" MISSING');
        }
        
        if (!isset($eventkeyarr[2])) {
            ResultParser::error('PARAM "EventKey" ERROR');
        }
        $admin_userid = $eventkeyarr[2];
        $db_admin_users = CDbuser::getInstanceTable('AdminUsers');
        $admin_userinfo = $db_admin_users->getUserById($admin_userid);
        if (!$admin_userinfo) {
            ResultParser::error('BIND ACCOUNT NOT EXISTS');
        }
        
        $wx_openid = HttpParam::post('FromUserName');
        if (!$wx_openid) {
            ResultParser::error('PARAM "FromUserName" MISSING');
        }
        
        $res = $db_admin_users->updateUser($admin_userinfo['platid'], $admin_userinfo['loginname'], $admin_userid, array('wx_bind'=>$wx_openid));
        if ($res) {
            ResultParser::succ('绑定微信成功!');
        }
        ResultParser::succ('绑定微信失败!');
    }
}