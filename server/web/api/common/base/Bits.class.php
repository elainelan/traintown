<?php
/**
 * 封装bit级别的数组与字符串相互转换
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * bit级别的字符串与数组相互转换类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class Bits
{

    /**
     * 字符串转换成数组
     *
     * @param string $str
     *        需要转换的字符串
     * @return array 转换后的数组，例如：[1,0,1,1,1,0...]
     */
    public static function decode($str)
    {
        $str_len = strlen($str);
        $bit_ary = array();
        for ($cur_len = 0; $cur_len < $str_len; ++$cur_len) {
            $unpack = unpack('C', substr($str, $cur_len));
            for ($i = 0; $i < 8; ++$i) {
                array_push($bit_ary, $unpack[1] >> $i & 0x01);
            }
        }
        return $bit_ary;
    }

    /**
     * 数组转换成字符串
     *
     * @param array $bit_ary
     *        需要转换的比特数组，例如：array(0=>1,3=>0,5=>1)，键值0,3,5对应bit位置
     * @return string 转换后的字符串
     */
    public static function encode($bit_ary)
    {
        // 空数组，直接返回
        $str = '';
        if ($bit_ary) {
            // 计算字符串长度
            $max_i = max(array_keys($bit_ary));
            $str_len = (int)(($max_i + 1) / 8);
            if (($max_i + 1) % 8) {
                ++$str_len;
            }
            // 每8位转换成1个byte，多个byte再相互连接组成字符串
            for ($cur_len = 0; $cur_len < $str_len; ++$cur_len) {
                $val = 0;
                for ($i = 0; $i < 8; ++$i) {
                    if ($bit_ary[$cur_len * 8 + $i]) {
                        $val += pow(2, $i);
                    }
                }
                $str .= pack("C", $val);
            }
        }
        return $str;
    }
}