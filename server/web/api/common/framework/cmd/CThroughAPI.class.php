<?php
/**
 * 和API通讯处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 和API通讯处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class CThroughAPI extends ThroughAPIBase
{
    /**
     * 
     * 通过API接口获取数据
     *
     * @param array $sids
     *        区服SID
     * @param integer $platid
     *        平台ID
     * @param array $post
     *        数据库[cmds:[{cmd_type=APICMD::SQL_SELECT, cmd_para={db=APICMD::DB_TABLE_GAME, sql}},...]
     *        服务器[cmds:[{cmd_type=APICMD::GAMESERVER, cmd_para},...]
     *        mem缓存[cmds:[{cmd_type=APICMD::MEMCACHE, cmd_para},...]
     *
     *        多种操作可以按顺序一步一步执行
     */
    public static function GSAPIMulti($gs_api, $sids, $platid, $post)
    {
        $res = array();     // 访问结果
        
        $db_game_servers = CDbuser::getInstanceTable('GameServers');
        //$db_servers = new Servers(); // for dev
        $servers = $db_game_servers->getBySids($sids);
        
        if ($servers) {
            
            $urls = array();    // 访问地址
            $posts = array();    // POST参数
            
            foreach ($servers as $sid => $server) {
                // urls组织
                $get = array('gmd' => $gs_api, 'sid' => $sid, 'platid' => $platid);
                // TODO: server访问方式：域名访问 or IP访问，现在暂时是IP访问
                $urls[$sid] = "http://{$server['srv_ip']}/" . API_GAME_INTERFACE . '?' . http_build_query($get);
            
                // POST数据组织
                $posts[$sid] = $post;
                $posts[$sid]['sid'] = $sid;
                $posts[$sid]['platid'] = $platid;
                
                // POST数据中需要传递server配置信息
                $server_keys = null;
                if ($server_keys) {
                    foreach ($server_keys as $key) {
                        $posts[$sid]['server'][$key] = $server[$key];
                    }
                }
                else {
                    $posts[$sid]['server'] = $server;
                }
            }
            $res = parent::postMulti($urls, API_GS_SIGN_KEY, array(), $posts);
        }
        return $res;
    }
    
    public static function DBAPIOne($db_api_url, $get=array(), $post=array())
    {
        return parent::postOne($db_api_url, API_DB_SIGN_KEY, $get, $post);
    }
    
    public static function CenterAPIOne($center_api_url, $get=array(), $post=array())
    {
        return parent::postOne($center_api_url, API_CENTER_SIGN_KEY, $get, $post);
    }
    
}
