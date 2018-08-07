<?php
/**
 * GServer类文件
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * GServer 类
 *
 * 封装memcache及db操作
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */
class GServer
{

    private static $s_gservers;

    private $game_port;

    private $conf_port;

    private $prt_port;

    private $timeout = 2;

    public function __construct($game_port, $conf_port = '', $prt_port = '')
    {
        $this->game_port = $game_port;
        $this->conf_port = $conf_port;
        $this->prt_port = $prt_port;
    }

    public static function getInstanceGServer($game_port, $conf_port = '', $prt_port = '')
    {
        $server_key = "{$game_port}_{$conf_port}_{$prt_port}";
        if (!self::$s_gservers[$server_key]) {
            self::$s_gservers[$server_key] = new GServer($game_port, $conf_port, $prt_port);
        }
        return self::$s_gservers[$server_key];
    }

    public function getFromConf($cmd, $post = array())
    {
        $url = 'http://127.0.0.1:' . $this->conf_port . '/do?cmd=' . $cmd;
        return $this->_getFromServer($url, $post);
    }

    public function getFromPrt($cmd, $post = array())
    {
        $url = 'http://127.0.0.1:' . $this->prt_port . '/do?cmd=' . $cmd;
        return $this->_getFromServer($url, $post);
    }

    public function getFromGame($cmd, $post = array())
    {
        $url = 'http://127.0.0.1:' . $this->game_port . '/do?cmd=' . $cmd;
        return $this->_getFromServer($url, $post);
    }

    private function _getFromServer($url, $post = array())
    {
        $data = json_encode($post);
        return Curl::postQueryOne($url, $data, 0, $this->timeout);
    }
}

?>
