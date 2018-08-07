<?php
/**
 * 微信服务号对接类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 微信企业号对接类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class CVWeChatService
{
    /**
     * 微信服务号ID（CorpID）
     */
    private $appid;

    /**
     * 微信服务号应用密钥（Secret）
     */
    private $secret;

    /**
     * 微信服务号应用密钥（Secret）
     */
    private $token;
    
    /**
     * 构造函数
     * 
     */
    public function __construct($appid, $secret)
    {
        $this->appid = $appid;
        $this->secret = $secret;
        $this->_setToken();
    }
    
    /**
     * 获取token
     * @param int $cache
     *        是否强制重新获取
     *        
     * @return void 
     */
    private function _setToken($cache=1)
    {
        $mem = CDbuser::getInstanceMemcache();
        $key = 'wechat_alert_token_'.$this->appid.'_'.$this->secret;
        $token = $mem->get($key);
        if ($token && $cache) {
            $this->token = $token;
            return ;
        }

        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appid."&secret=".$this->secret;
        $res = Curl::getQueryOne($url);
        if ($res['r']) {
            $data = $res['res'];
            if ($data && !$data['errcode']) {
                //保存token
                $mem->set($key, $data['access_token'], $data['expires_in']);
                $this->token = $data['access_token'];
                return ;
            }
            CVLog::addlog('WeChatFuWu_error', array('url' => $url, 'res' => json_encode($res['res'])));
            return ;
        }
        CVLog::addlog('WeChatFuWu_curl_error', array('url' => $url, 'error' => $res['error']));
        return ;
    }
    

    /**
     * 获取带参数的二维码url
     */
    public function getQrcodeUrl($param, $token_expire_retry=1)
    {
        if (!$this->token) {
            return null;
        }
        $post = array(
            'expire_seconds'    =>  '1800',
            'action_name'       =>  'QR_STR_SCENE',
            'action_info'       =>  $param
        );
        //return $this->token;
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$this->token;
        
        $res = Curl::postQueryOne($url, json_encode($post));

        if ($res['r']) {
            $data = $res['res'];
            if ($data && !$data['errcode']) {
                return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$data['ticket'];
            }
            else if ($data['errcode'] == 40014 && $token_expire_retry) {
                //token过期，重试一次
                $this->_setToken(0);
                return $this->getQrcodeTicket($param, 0);
            }
            CVLog::addlog('WeChatFuWu_error', array('url' => $url, 'res' => json_encode($res['res']), 'post' => $post));
            return null;
        }
        CVLog::addlog('WeChatFuWu_curl_error', array('url' => $url, 'error' => $res['error']));
        return null;
    }
    
    /**
     * 发送消息
     */
    public function send($post, $token_expire_retry=1)
    {
        // TODO: 异步实现
        
        if (!$this->token) {
            return false;
        }
        $post['agentid'] = $this->agentid;
        
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->token;
        
        $res = Curl::postQueryOne($url, json_encode($post));
        if ($res['r']) {
            $data = $res['res'];
            if ($data && !$data['errcode']) {
                return true;
            }
            else if ($data['errcode'] == 40014 && $token_expire_retry) {
                //token过期，重试一次
                $this->_setToken(0);
                return $this->send($post, 0);
            }
            CVLog::addlog('WeChatFuWu_error', array('url' => $url, 'res' => json_encode($res['res']), 'post' => $post));
            return false;
        }
        CVLog::addlog('WeChatFuWu_curl_error', array('url' => $url, 'error' => $res['error']));
        return false;
    }
    
}

