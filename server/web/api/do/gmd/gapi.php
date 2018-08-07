<?php
/**
 * gapi类文件
 *
 * @author dragonets
 * @package do
 * @subpackage gmd
 */

if (!defined('CV_ROOT')) {
    exit('No direct script access allowed');
}

/**
 * gapi操作类
 *
 * @author dragonets
 * @package do
 * @subpackage gmd
 */
class gapi extends GDoBase
{
    /**
     * 构造函数，实例化的时候进行数据验证
     * post数据解密后存到了GSAPI::$data中，可以直接使用
     */
    public function __construct()
    {
        parent::decrypt();
    }
    
    /**
     * 接收请求接口
     */
    public function api()
    {
        if (is_array(GSAPI::$data['cmds'])) {
            $res = array();
            foreach (GSAPI::$data['cmds'] as $cmd_key => $cmd) {
                switch ($cmd['cmd_type']) {
                    case APICMD::SQL_SELECT:
                        $res[$cmd_key] = GSAPISql::get($cmd['cmd_para']);
                        break;
                    case APICMD::SQL_MOD:
                        $res[$cmd_key] = GSAPISql::exec($cmd['cmd_para']);
                        break;
                        
                    case APICMD::GAMESERVER_CONF:
                        $res[$cmd_key] = GSAPIGS::getFromConf($cmd['cmd_para']);
                        break;
                    case APICMD::GAMESERVER_GAME:
                        $res[$cmd_key] = GSAPIGS::getFromGame($cmd['cmd_para']);
                        break;
                    case APICMD::GAMESERVER_PRT:
                        $res[$cmd_key] = GSAPIGS::getFromPrt($cmd['cmd_para']);
                        break;
                        
                    case APICMD::MEMCACHE:
                        $res[$cmd_key] = GSAPIMemcache::get($cmd['cmd_para']);
                        break;
                    default:
                        break;
                }
            }
            ResultParser::succ(Sign::encryptData($res));
        }
        ResultParser::error(GErrorCode::API_MISS_CMDS);
    }

}