<?php
/**
 * AdminMessage-后台消息相关处理
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 后台消息处理相关封装类
 *
 * @author dragonets
 * @package common
 * @subpackage application/cmd
 */
class AdminMessage
{

    private static $wechat;

    private static function _init()
    {
        // 微信初始化
        if (!self::$wechat) {
            // "weixin_corpid":"","weixin_agentid":"","weixin_agentsecret":""
            if (AdminSetting::$admin_settings['weixin_service_appid'] && AdminSetting::$admin_settings['weixin_service_appsecret']) {
                self::$wechat = new CVWeChatService(AdminSetting::$admin_settings['weixin_service_appid'], AdminSetting::$admin_settings['weixin_service_appsecret']);
            }
        }
    }

    /**
     * 登录消息
     *
     * @param array $info
     *        登录信息： array('wxuser'=>'', 'loginname'=>'', 'logintime'=>'', 'loginip'=>'')
     */
    public static function login($info)
    {
        self::_init();
        if (self::$wechat && AdminSetting::$admin_settings['weixin_service_login_template_id']) {

            $data = array(
                'first'  =>  array(
                    'value' =>  "你的《".AdminSetting::$admin_settings['weixin_service_game_admin_name']."》后台账号\"".$info['loginname']."\"进行了登录操作\r\n",
                    //"color" =>  "#173177",
                ),
                'keyword1'  =>  array(
                    'value' =>  date('Y-m-d H:i:s', $info['logintime']),
                    //"color" =>  "#173177",
                ),
                'keyword2'  =>  array(
                    'value' =>  $info['loginip'],
                    //"color" =>  "#173177",
                ),
                'remark'    =>  array(
                    'value' =>  "\r\n如有疑问，请联系后台管理员",
                    //"color" =>  "#173177",
                ),
            );
            
            $post = array(
                'touser'        => $info['wxuser'],
                'template_id'   => AdminSetting::$admin_settings['weixin_service_login_template_id'],
                //'url'           => 'http://10.1.8.132/new/views',
                'data'          => $data,
            );
            
            self::$wechat->send($post);
        }
    }

}