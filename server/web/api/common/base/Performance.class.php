<?php
/**
 * 性能评估
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 性能评估类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class Performance
{

    /**
     * 处理信息
     *
     * @var array
     */
    private static $parse;

    /**
     * 获取微妙浮点数
     *
     * @return number
     */
    private static function _get_microtime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * 设置统计开始时间
     *
     * @param string $key        
     */
    public static function setStartTime($key)
    {
        self::$parse[$key]['start'] = self::_get_microtime();
    }

    /**
     * 设置统计结束时间
     *
     * @param string $key        
     */
    public static function setEndTime($key)
    {
        self::$parse[$key]['end'] = self::_get_microtime();
    }

    /**
     * 返回性能数据数组
     *
     * @param string $key        
     */
    public static function getParseData($key)
    {
        if (empty(self::$parse[$key]['start'])) {
            return false;
        }
        if (empty(self::$parse[$key]['end'])) {
            self::setEndTime($key);
        }
        
        $data = self::$parse[$key];
        
        $data['key'] = $key;
        $data['uri'] = HttpParam::server('REQUEST_URI');
        $data['parse'] = $data['end'] - $data['start'];
        $data['memory_usage_mb'] = sprintf('%0.2f', memory_get_usage() / 1024 / 1024);
        
        return $data;
    }

}