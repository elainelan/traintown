<?php
/**
 * CURL网络请求处理
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 网络请求处理类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class Curl
{

    /**
     * 单个POST请求
     *
     * @param string $url
     *        请求URL地址
     * @param array|string $post
     *        POST数据
     * @param number $json
     *        请求接口是否返回json数据
     * @param number $timeout
     *        请求超时时间
     * @return null|array array('r'=>0, 'error'=>'')|array('r'=>1, 'res'=>'')
     */
    public static function postQueryOne($url, $post = array(), $json = 1, $timeout = 60)
    {
        return self::_queryOne($url, $post, $json, $timeout, 'post');
    }

    /**
     * 单个GET请求
     *
     * @param string $url
     *        请求URL地址
     * @param array|string $get
     *        GET数据
     * @param number $json
     *        请求接口是否返回json数据
     * @param number $timeout
     *        请求超时时间
     * @return null|array array('r'=>0, 'error'=>'')|array('r'=>1, 'res'=>'')
     */
    public static function getQueryOne($url, $get = array(), $json = 1, $timeout = 60)
    {
        return self::_queryOne($url, $get, $json, $timeout, 'get');
    }

    /**
     * 当个HTTP(GET/POST)请求
     *
     * @param string $url
     *        请求URL地址
     * @param array|string $param
     *        请求参数
     * @param number $json
     *        请求接口是否返回json数据
     * @param number $timeout
     *        请求超时时间
     * @param string $type
     *        请求类型：post / get
     * @return NULL|array array('r'=>0, 'error'=>'')|array('r'=>1, 'res'=>'')
     */
    private static function _queryOne($url, $param = array(), $json = 1, $timeout = 60, $type = 'post')
    {
        if (!$url || !in_array($type, array('post', 'get'))) {
            return null;
        }
        
        $ch = curl_init();
        
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($param) ? http_build_query($param) : $param);
        }
        else {
            curl_setopt($ch, CURLOPT_URL, $url . http_build_query($param));
        }
        
        if ($timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // https
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return array('r' => 0, 'error' => $error);
        }
        if ($json) {
            $json_result = json_decode($result, true);
            if (!$json_result) {
                return array('r' => 0, 'error' => $result);
            }
            $result = $json_result;
        }
        return array('r' => 1, 'res' => $result);
    }

    /**
     * 并发POST请求
     *
     * @param array $urls
     *        请求URL地址数组
     * @param array|string $post
     *        POST数据
     * @param number $json
     *        请求接口是否返回json数据
     * @param number $timeout
     *        请求超时时间
     * @param string $type
     *        请求类型 post / post_diff
     * @return NULL|array array('urls_array_key' => array('r'=>0, 'error'=>''))|array('urls_array_key' => array('r'=>1, 'res'=>''))
     */
    public static function postQueryMulti($urls, $post = array(), $json = 1, $timeout = 60, $type = 'post')
    {
        return self::_queryMulti($urls, $post, $json, $timeout, $type);
    }

    /**
     * 并发GET请求
     *
     * @param array $urls
     *        请求URL地址数组
     * @param array|string $get
     *        GET数据
     * @param number $json
     *        请求接口是否返回json数据
     * @param number $timeout
     *        请求超时时间
     * @return NULL|array array('urls_array_key' => array('r'=>0, 'error'=>''))|array('urls_array_key' => array('r'=>1, 'res'=>''))
     */
    public static function getQueryMulti($urls, $get = array(), $json = 1, $timeout = 60)
    {
        return self::_queryMulti($urls, $get, $json, $timeout, 'get');
    }

    /**
     * 并发HTTP(POST/GET)请求
     *
     * @param array $urls
     *        请求URL地址数组
     * @param array|string $param
     *        请求数据
     * @param number $json
     *        请求接口是否返回json数据
     * @param number $timeout
     *        请求超时时间
     * @param string $type
     *        请求类型： post / get / post_diff
     * @return NULL|array array('urls_array_key' => array('r'=>0, 'error'=>''))|array('urls_array_key' => array('r'=>1, 'res'=>''))
     */
    private static function _queryMulti($urls, $param = array(), $json = 1, $timeout = 60, $type = 'post')
    {
        if (!is_array($urls) || count($urls) == 0 || !in_array($type, array('post', 'get', 'post_diff'))) {
            return null;
        }
        // 开启多线程curl
        $mh = curl_multi_init();
        // 设置多线程curl参数
        foreach ($urls as $k => $url) {
            $ch[$k] = curl_init();
            if ($type == 'post') {
                curl_setopt($ch[$k], CURLOPT_URL, $url);
                curl_setopt($ch[$k], CURLOPT_POST, 1);
                curl_setopt($ch[$k], CURLOPT_POSTFIELDS, is_array($param) ? http_build_query($param) : $param);
            }
            else if ($type == 'post_diff') {
                curl_setopt($ch[$k], CURLOPT_URL, $url);
                curl_setopt($ch[$k], CURLOPT_POST, 1);
                curl_setopt($ch[$k], CURLOPT_POSTFIELDS, is_array($param[$k]) ? http_build_query($param[$k]) : $param[$k]);
            }
            else if ($type == 'get') {
                $url_append = '';
                if ($param) {
                    $with = '?';
                    if (strpos($url, $with)) {
                        $with = '&';
                    }
                    $url_append = $with . http_build_query($param);
                }
                curl_setopt($ch[$k], CURLOPT_URL, $url . $url_append);
            }
            if ($timeout) {
                curl_setopt($ch[$k], CURLOPT_TIMEOUT, $timeout);
            }
            curl_setopt($ch[$k], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch[$k], CURLOPT_SSL_VERIFYPEER, false); // https
            curl_multi_add_handle($mh, $ch[$k]);
        }
        // 执行多线程curl
        $mrc = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        }
        while ($mrc == CURLM_CALL_MULTI_PERFORM);
        
        while ($active and $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                }
                while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        // 获取结果并释放连接
        $datas = array();
        foreach ($urls as $k => $v) {
            $error = curl_error($ch[$k]);
            if ($error) {
                $datas[$k] = array('r' => 0, 'error' => $error);
                $log_param = $type == 'post_diff' ? $param[$k] : $param;
                CVLog::addlog('CURL_fail', array('type' => $type, 'error' => $error, 'url' => $urls[$k], 'data' => $log_param));
            }
            else {
                $curl_res = curl_multi_getcontent($ch[$k]);
                if (defined('LOG_CURL') && LOG_CURL) {
                    $log_param = $type == 'post_diff' ? $param[$k] : $param;
                    CVLog::addlog('CURL_succ', array('type' => $type, 'error' => $error, 'url' => $urls[$k], 'data' => $log_param));
                }
                
                $datas[$k] = array('r' => 1, 'res' => $curl_res);
                if ($json) {
                    $json_datas = json_decode($curl_res, true);
                    if (!$json_datas) {
                        $datas[$k] = array('r' => 0, 'error' => $curl_res);
                    }
                    else {
                        $datas[$k] = array('r' => 1, 'res' => $json_datas);
                    }
                }
            }
            curl_multi_remove_handle($mh, $ch[$k]);
        }
        curl_multi_close($mh);
        // 返回结果
        return $datas;
    }
}

