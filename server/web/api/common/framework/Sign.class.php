<?php
/**
 * 请求sign处理
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 网络请求sign处理类
 *
 * @author dragonets
 * @package common
 * @subpackage framework
 */
class Sign
{

    /**
     * 生成sign
     *
     * @param array $http_param
     *        参数数组
     * @param string $sign_key
     *        加密key
     * @return string 计算出来的sign
     */
    public static function generalSign($http_param, $sign_key)
    {
        return md5(http_build_query($http_param) . $sign_key);
    }

    /**
     * 检查sign
     *
     * @param array $http_param
     *        参数数组
     * @param string $sign_key
     *        加密key
     * @return boolean
     */
    public static function validSign($http_param, $sign_key)
    {
        if (empty($http_param['sign'])) {
            return false;
        }
        $sign = $http_param['sign'];
        unset($http_param['sign']);
        if ($sign != self::generalSign($http_param, $sign_key)) {
            return false;
        }
        return true;
    }

    /**
     * 数据加密，注意，在算generalSign之前就需要加密
     *
     * @param string $data        
     * @param string $key        
     * @return string
     */
    private static function _encrypt($data, $key)
    {
        $char = '';
        $str = '';
        
        $key = md5($key);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $key{$x};
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
        }
        return $str;
    }

    /**
     * 数据解密
     *
     * @param string $data        
     * @param string $key        
     * @return string
     */
    private static function _decrypt($data, $key)
    {
        $char = null;
        $str = null;
        
        $key = md5($key);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            }
            else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }

    /**
     * 加密数据
     *
     * @param string $data        
     * @return string data=xxx
     */
    public static function encryptData($data)
    {
        return base64_encode(self::_encrypt(gzcompress(serialize($data)), API_DATA_KEY));
    }

    /**
     * 解密数据
     *
     * @param string $data
     *        原始加密数据
     * @return mixed 解码失败返回false，成功返回解码后的数据
     */
    public static function decryptData($data)
    {
        $data = base64_decode($data);
        if ($data !== false) {
            $data = gzuncompress(self::_decrypt($data, API_DATA_KEY));
            if ($data !== false) {
                $data = unserialize($data);
            }
        }
        return $data;
    }
}

