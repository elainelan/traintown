<?php
/**
 * GSAPIGS通讯处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * GSAPIGS通讯处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */
class GSAPIGS
{

    private static $game_port;

    private static $conf_port;

    private static $prt_port;

    private static $cmd;

    private static $port;

    private static $post;

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
        self::$game_port = GSAPI::$data['server']['game_port'];
        self::$conf_port = GSAPI::$data['server']['srv_conf_port'];
        self::$prt_port = GSAPI::$data['server']['srv_prt_port'];
        
        self::$port = $cmd_para['port'];
        self::$cmd = $cmd_para['cmd'];
        self::$post = $cmd_para['post'];
    
    }

}

