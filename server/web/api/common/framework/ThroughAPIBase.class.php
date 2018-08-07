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
class ThroughAPIBase
{

    /**
     * API请求超时时间5s
     *
     * @var number
     */
    protected static $timeout = 3;

    /**
     * API返回数据格式是否JSON
     *
     * @var number
     */
    protected static $json = 1;

    /**
     * 单个请求处理
     *
     * @param string $url
     *        请求url
     * @param string $key
     *        GET参数签名key
     * @param array $get
     *        GET参数
     * @param array $post
     *        POST数据
     *        
     * @return array array('r'=>1, 'res'=>..)
     */
    protected static function postOne($url, $key, $get = array(), $post = array())
    {
        $api_res = array();
        
        // url签名
        $url = self::_get_signed_url($url, $key, $get);
        // post加密
        $post = self::_encrypt_post($post);
        // 发送请求
        $curl_res = Curl::postQueryOne($url, $post, self::$json, self::$timeout);
        // 请求结果
        if (!empty($curl_res['r'])) {
            if ($curl_res['res']['r'] && $data = Sign::decryptData($curl_res['res']['data'])) {
                $curl_res['res']['data'] = $data;
            }
            $api_res = $curl_res['res'];
        }
        else {
            $api_res['r'] = 0;
            $api_res['errCode'] = ErrorCode::CURL_RES_ERROR;
            $api_res['errMsg'] = $curl_res['error'];
        }
        
        return $api_res;
    }

    /**
     * 并发请求处理
     *
     * @param array $urls
     *        请求数组
     * @param string $key
     *        GET参数签名key
     * @param array $gets
     *        GET参数，以urls的索引key为键值
     * @param array $posts
     *        POST数据，以urls的索引key为键值
     *        
     * @return array array('urls索引key' => array('r'=>1, 'res'=>..) ...)
     */
    protected static function postMulti($urls, $key, $gets = array(), $posts = array())
    {
        $api_res = array();
        
        // urls签名
        foreach ($urls as $k => &$url) {
            $url = self::_get_signed_url($url, $key, $gets[$k] ? $gets[$k] : array());
        }
        // posts加密
        foreach ($posts as &$post) {
            $post = self::_encrypt_post($post);
        }
        // 发送请求
        $curls_res = Curl::postQueryMulti($urls, $posts, self::$json, self::$timeout, 'post_diff');
        // 请求结果
        foreach ($curls_res as $k => &$v) {
            if (!empty($v['r'])) {
                if ($v['res']['r'] && $data = Sign::decryptData($v['res']['data'])) {
                    $v['res']['data'] = $data;
                }
                $api_res[$k] = $v['res'];
            }
            else {
                $api_res[$k]['r'] = 0;
                $api_res[$k]['errCode'] = ErrorCode::CURL_RES_ERROR;
                $api_res[$k]['errMsg'] = $v['error'];
            }
        }
        
        return $api_res;
    }

    /**
     * 加密post数据
     *
     * @param array $post
     *        原始数据
     * @return string 'data=xxxx'
     */
    private static function _encrypt_post($post)
    {
        return 'data=' . urlencode(Sign::encryptData($post));
    }

    /**
     * 获取签名URL
     *
     * @param string $url
     *        请求地址
     * @param string $key
     *        sign计算key
     * @param array $get
     *        额外GET参数数组
     *        
     * @return string 签名后的url
     */
    private static function _get_signed_url($url, $key, $get = array())
    {
        $url_parse = parse_url($url);
        
        $url_get = array(); // GET参数数组
        if (isset($url_parse['query'])) {
            parse_str($url_parse['query'], $url_get);
        }
        $url_get = array_merge($url_get, array('_t' => time()), $get);
        
        // 重构URL
        return $url_parse['scheme'] . '://' . $url_parse['host'] . $url_parse['path'] . '?' . http_build_query($url_get) . '&sign=' . Sign::generalSign($url_get, $key);
    }
    
    
}