<?php
/**
 * GSAPIMemcache通讯处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * GSAPIMemcache通讯处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */
class GSAPIMemcache
{

    private static $s_memcache;

    private static $server_id;

    private static $cmd;

    private static $params;

    /**
     * 获取数据
     *
     * @param array $cmd_para        
     */
    public static function getFromConf($cmd_para)
    {
        self::_parsePara($cmd_para);
        return GServer::getInstanceGServer(self::$game_port, self::$conf_port, self::$prt_port)->getFromConf(self::$cmd, self::$post);
    }

    public static function getFromPrt($cmd_para)
    {
        self::_parsePara($cmd_para);
        return GServer::getInstanceGServer(self::$game_port, self::$conf_port, self::$prt_port)->getFromPrt(self::$cmd, self::$post);
    }

    public static function getFromGame($cmd_para)
    {
        self::_parsePara($cmd_para);
        return GServer::getInstanceGServer(self::$game_port, self::$conf_port, self::$prt_port)->getFromGame(self::$cmd, self::$post);
    }

    /**
     * 处理cmd_para参数
     *
     * @param array $cmd_para        
     */
    private static function _parsePara($cmd_para)
    {
        self::$server_id = GSAPI::$data['server']['sid'];
        self::$s_memcache = GDbuser::getInstanceMemcache(self::$server_id);
        
        self::$cmd = $cmd_para['cmd'];
        self::$params = $cmd_para['params'];
    }

    public static function get($cmd_para)
    {
        self::_parsePara($cmd_para);
        
        $method = self::$cmd;
        $params = self::$params;
        
        switch ($method) {
            case 'add':
                return self::$s_memcache->get($params['key'], $params['val'], $params['flag'], $params['expire']);
                break;
            case 'get':
                return self::$s_memcache->get($params['key']);
                break;
            case 'set':
                return self::$s_memcache->set($params['key'], $params['val'], $params['expire']);
                break;
            case 'delete':
                return self::$s_memcache->delete($params['key']);
                break;
            case 'decrement':
                return self::$s_memcache->decrement($params['key'], $params['val']);
                break;
            case 'increment':
                return self::$s_memcache->increment($params['key'], $params['val']);
                break;
            case 'flush':
                return self::$s_memcache->flush();
                break;
            case 'replace':
                return self::$s_memcache->replace($params['key'], $params['val'], $params['flag'], $params['expire']);
                break;
        }
        ResultParser::error(GErrorCode::PARAM_ERROR);
    }
}

