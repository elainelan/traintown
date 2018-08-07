<?php
/**
 * 微信企业号对接类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 * @deprecated
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
class CVWeChatCorp
{
    /**
     * 企业微信号ID（CorpID）
     */
    private $corpid;

    /**
     * 企业微信号应用ID（AgentId）
     */
    private $agentid;

    /**
     * 企业微信号应用密钥（Secret）
     */
    private $secret;

    /**
     * 企业微信号应用密钥（Secret）
     */
    private $token;
    
    /**
     * 构造函数
     * 
     */
    public function __construct($corpid, $agentid, $secret)
    {
        $this->corpid = $corpid;
        $this->agentid = $agentid;
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
        $key = 'wechat_alert_token_'.$this->corpid.'_'.$this->corpsecret;
        $token = $mem->get($key);
        if ($token && $cache) {
            $this->token = $token;
            return ;
        }

        $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=".$this->corpid."&corpsecret=".$this->secret;
        $res = Curl::getQueryOne($url);
        if ($res['r']) {
            $data = $res['res'];
            if ($data && !$data['errcode']) {
                //保存token
                $mem->set($key, $data['access_token'], $data['expires_in']);
                $this->token = $data['access_token'];
                return ;
            }
            CVLog::addlog('WeChat_error', array('url' => $url, 'res' => json_encode($res['res'])));
            return ;
        }
        CVLog::addlog('WeChat_curl_error', array('url' => $url, 'error' => $res['error']));
        return ;
    }
    
    /**
     * 获取可发送消息的成员ID与部门ID
     */
    private function _getAgent($token_expire_retry=1)
    {
        if (!$this->token) {
            return false;
        }
        
        $url = "https://qyapi.weixin.qq.com/cgi-bin/agent/get?access_token=".$this->token."&agentid=".$this->agentid;
        $res = Curl::getQueryOne($url);
        if ($res['r']) {
            $data = $res['res'];
            if ($data && !$data['errcode']) {
                return $data;
            }
            else if ($data['errcode'] == 42001 && $token_expire_retry) {
                //token过期，重试一次
                $this->_setToken(0);
                return $this->_getAgent(0);
            }
            CVLog::addlog('WeChat_error', array('url' => $url, 'res' => json_encode($res['res'])));
            return false;
        }
        CVLog::addlog('WeChat_curl_error', array('url' => $url, 'error' => $res['error']));
        return false;
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
        
        $url = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=".$this->token;
        
        $res = Curl::postQueryOne($url, json_encode($post));
        if ($res['r']) {
            $data = $res['res'];
            if ($data && !$data['errcode']) {
                return true;
            }
            else if ($data['errcode'] == 42001 && $token_expire_retry) {
                //token过期，重试一次
                $this->_setToken(0);
                return $this->send($post, 0);
            }
            CVLog::addlog('WeChat_error', array('url' => $url, 'res' => json_encode($res['res']), 'post' => $post));
            return false;
        }
        CVLog::addlog('WeChat_curl_error', array('url' => $url, 'error' => $res['error']));
        return false;
    }
    
}

