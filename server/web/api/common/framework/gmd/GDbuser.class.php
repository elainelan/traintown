<?php
/**
 * GDbuser类文件
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * GDbuser 类
 *
 * 封装memcache及db操作
 *
 * @author dragonets
 * @package common
 * @subpackage framework/gmd
 */
class GDbuser
{

    /**
     * Memcache连接实例索引ID
     *
     * @var string
     */
    const CACHE_CLUSTER_ID = 'id_gs_api';

    /**
     * Memecache缓存key前缀
     *
     * @var string
     */
    const CACHE_PREFIX = 'gs_api_';

    /**
     * memcache默认缓存时间，单位秒，默认10800（3小时）
     *
     * @var int
     */
    const CACHE_TIME = 10800;

    /**
     * 数据库连接指针
     *
     * @var object CVDB
     */
    private static $s_db;

    /**
     * Memecache连接指针
     *
     * @var object CVMemcache
     */
    private static $s_memcache;

    /**
     * Redis连接指针
     *
     * @var object CVRedis
     */
    private static $s_redis;

    /**
     * 数据库表类实例化指针数组
     *
     * @var array
     */
    private static $s_table;

    /**
     * 获取Memcache连接object（CVMemcache）
     */
    public static function getInstanceMemcache($server_id)
    {
        if (!self::$s_memcache[$server_id]) {
            self::$s_memcache[$server_id] = CVMemcache::getInstance(self::CACHE_CLUSTER_ID . '_' . $server_id);
        }
        return self::$s_memcache[$server_id];
    }

    /**
     * 获取DB连接object（CVDbPdo）
     */
    public static function getInstanceDb($db_ip, $db_name, $db_user, $db_pwd)
    {
        $db_key = "{$db_ip}_{$db_name}_{$db_user}";
        if (!self::$s_db[$db_key]) {
            self::$s_db[$db_key] = CVDbPdo::getInstance($db_ip, $db_name, $db_user, $db_pwd);
        }
        return self::$s_db[$db_key];
    }

    /**
     * 获取Redis连接object（Redis）
     */
    public static function getInstanceRedis()
    {
        if (!self::$s_redis) {
            // TODO：后续使用Redis重新调整
            self::$s_redis = CVRedis::getInstance(self::CACHE_CLUSTER_ID);
        }
        return self::$s_redis;
    }

    /**
     * 获取数据库表操作类指针
     *
     * @param string $table_class_name
     *        需要获取指针的类名称
     */
    public static function getInstanceTable($table_class_name)
    {
        if (empty(self::$s_table[$table_class_name])) {
            self::$s_table[$table_class_name] = new $table_class_name();
        }
        return self::$s_table[$table_class_name];
    }

    /**
     * 内存加锁
     *
     * @param string $key
     *        加锁key
     * @param string $value
     *        加锁值
     * @param number $expire_time
     *        加锁时间
     * @param number $wait
     *        等待获取锁时间，默认60秒
     */
    public static function lock($server_id, $key, $value, $expire_time = 120, $wait = 60) // 锁定2分钟
    {
        return self::getInstanceMemcache($server_id)->lock($key, $value, $expire_time, $wait);
    }

    /**
     * 解锁
     *
     * @param string $key
     *        解锁key
     */
    public static function unlock($server_id, $key)
    {
        return self::getInstanceMemcache($server_id)->unlock($key);
    }

}

?>
