<?php
/**
 * CDbuser类文件
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * CDbuser 类
 *
 * 封装memcache及db操作
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class CDbuser
{

    /**
     * Memcache连接实例索引ID
     *
     * @var string
     */
    const CACHE_CLUSTER_ID = 'id_center_api';

    /**
     * Memecache缓存key前缀
     *
     * @var string
     */
    const CACHE_PREFIX = 'center_api_';

    /**
     * memcache默认缓存时间，单位秒，默认10800（3小时）
     *
     * @var int
     */
    const CACHE_TIME = 10800;

    /**
     * 数据库连接指针
     *
     * @var CVDbPdo
     */
    private static $s_db_pdo;
    
    /**
     * 数据库连接指针
     * 
     * @var CCVDbHttp
     */
    private static $s_db_http;

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
    public static function getInstanceMemcache()
    {
        if (!self::$s_memcache) {
            self::$s_memcache = CVMemcache::getInstance(self::CACHE_CLUSTER_ID);
        }
        return self::$s_memcache;
    }

    /**
     * 获取DB连接object（CVDbPdo）
     */
    public static function getInstanceDbPdo()
    {
        if (!self::$s_db_pdo) {
            self::$s_db_pdo = CVDbPdo::getInstance(DB_IP, DB_NAME, DB_USER, DB_PWD);
        }
        return self::$s_db_pdo;
    }
    
    public static function getInstanceDbHttp($table)
    {
        if (!self::$s_db_http) {
            self::$s_db_http = CCVDbHttp::getInstance(API_DB_INTERFACE, $table);
        }
        return self::$s_db_http;
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
    public static function lock($key, $value, $expire_time = 120, $wait = 60) // 锁定2分钟
    {
        return self::getInstanceMemcache()->lock($key, $value, $expire_time, $wait);
    }

    /**
     * 解锁
     *
     * @param string $key
     *        解锁key
     */
    public static function unlock($key)
    {
        return self::getInstanceMemcache()->unlock($key);
    }

}

?>
