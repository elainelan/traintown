<?php
/**
 * 数据验证类
 *
 * @author whx
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 数据验证基础类
 *
 * @author whx
 * @package common
 * @subpackage base
 */
class CVDataValidate
{

    private static $rules = array(
        // 不能为空
        'require' => '/\S+/', 
        // 邮件地址
        'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', 
        // 网络URL
        'url' => '/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(:\d+)?(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/', 
        // 货币金额
        'currency' => '/^\d+(\.\d+)?$/', 
        // 0和正整数
        'number' => '/^\d+$/', 
        // 邮政编码
        'zip' => '/^\d{6}$/', 
        // 有符号整数（含0）
        'integer' => '/^[-\+]?\d+$/', 
        // 有符号浮点数
        'double' => '/^[-\+]?\d+(\.\d+)?$/', 
        // 英文
        'english' => '/^[A-Za-z]+$/',
        
    );

    /**
     * 使用正则验证数据
     *
     * @access private
     * @param string $value
     *        要验证的数据
     * @param string $rule
     *        验证规则KEY/验证正则表达式
     * @return boolean 匹配返回true，不匹配false
     */
    private static function _regex($value, $rule)
    {
        // 检查是否有内置的正则表达式
        if (isset(self::$rules[$rule])) {
            $rule = self::$rules[$rule];
        }
        return preg_match($rule, $value) === 1;
    }

    /**
     * 验证数据 支持 in between equal length regex expire ip_allow ip_deny
     *
     * @access public
     * @param string $value
     *        验证数据
     * @param mixed $rule
     *        验证规则
     * @param string $type
     *        验证方式 默认为正则验证
     * @return boolean true：验证成功，不返回错误；false: 验证失败，输出错误
     */
    public static function check($value, $rule, $type = 'regex', $reverse = false)
    {
        $res = false;
        switch ($type) {
            case 'in': // 验证是否在某个指定范围之内 逗号分隔字符串或者数组
            case 'notin':
                $range = is_array($rule) ? $rule : explode(',', $rule);
                $res = $type == 'in' ? in_array($value, $range) : !in_array($value, $range);
                break;
            case 'between': // 验证是否在某个范围
            case 'notbetween': // 验证是否不在某个范围
                if (is_array($rule)) {
                    $min = $rule[0];
                    $max = $rule[1];
                }
                else {
                    list($min, $max) = explode(',', $rule);
                }
                $res = $type == 'between' ? $value >= $min && $value <= $max : $value < $min || $value > $max;
                break;
            case 'equal': // 验证是否等于某个值
            case 'notequal': // 验证是否等于某个值
                $res = $type == 'equal' ? $value == $rule : $value != $rule;
                break;
            case 'length': // 验证长度
                $length = mb_strlen($value, 'utf-8'); // 当前数据长度
                if (is_array($rule)) { // 长度区间
                    list($min, $max) = $rule;
                    $res = $length >= $min && $length <= $max;
                }
                else { // 指定长度
                    $res = $length == $rule;
                }
                break;
            case 'func':
                $res = call_user_func_array($rule, array($value));
                break;
            case 'regex':
            default: // 默认使用正则验证 可以使用验证类中定义的验证名称
                // 检查附加规则
                $res = self::_regex($value, $rule);
                break;
        }
        if ($reverse) {
            return !$res;
        }
        return $res;
    }
}