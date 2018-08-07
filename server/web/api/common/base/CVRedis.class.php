<?php
/**
 * 封装redis操作
 *
 * @author whx
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * redis操作类
 *
 * @author whx
 * @package common
 * @subpackage base
 */
class CVRedis
{

    /**
     * redis连接instance的数组
     *
     * @var array
     */
    private static $s_instance = array();

    /**
     * redis连接实例
     *
     * @var object redis
     */
    private $red_obj;

    /**
     * redis连接实例索引
     *
     * @var int|string
     */
    private $r_cluster_id;

    /**
     * 打开一个redisd服务端连接
     *
     * @param int|string $cluster_id
     *        实例索引。
     * @param string $host
     *        redisd服务端监听主机地址。
     * @param int $port
     *        redisd服务端监听端口。
     * @param int $threshold
     *        无意义，为了更好的替换memcache所做的的兼容
     * @param float $min_saving
     *        无意义，为了更好的替换memcache所做的的兼容
     * @return void
     */
    public function __construct($cluster_id, $host = REDIS_IP, $port = REDIS_PORT, $threshold = 0, $min_saving = 0.2)
    {
        $this->m_cluster_id = $cluster_id;
        $this->red_obj = new redis();
        $this->red_obj->connect($host, $port);
        /*
         * if ($threshold && $min_saving) {
         * $this->red_obj->setCompressThreshold($threshold, $min_saving);
         * }
         */
    }

    /**
     * 获取一个redisd服务端连接
     *
     * @param int|string $cluster_id
     *        实例索引。
     * @param string $host
     *        redisd服务端监听主机地址。
     * @param int $port
     *        redisd服务端监听端口。
     * @param int $threshold
     *        无意义，为了更好的替换memcache而保留
     * @param float $min_saving
     *        无意义，为了更好的替换memcache而保留
     * @return object redis redisd服务端连接实例。
     */
    public static function &getInstance($cluster_id, $host = REDIS_IP, $port = REDIS_PORT, $threshold = 65536, $min_saving = 0.2)
    {
        if (empty(self::$s_instance[$host . $port . $cluster_id])) {
            self::$s_instance[$host . $port . $cluster_id] = new CVRedis($cluster_id, $host, $port, $threshold, $min_saving);
        }
        return self::$s_instance[$host . $port . $cluster_id];
    }

    /**
     * 在服务器上存储值
     *
     * @param string $key
     *        要设置值的key。
     * @param mixed $val
     *        要存储的值，字符串和数值直接存储，其他类型序列化后存储。
     * @param int $expire
     *        当前写入缓存的数据的失效时间。如果此值设置为0表明此数据永不过期。你可以设置一个UNIX时间戳或 以秒为单位的整数（从当前算起的时间差）来说明此数据的过期时间，但是在后一种设置方式中，不能超过 2592000秒（30天）。
     * @param int $flag
     *        无意义，为了更好的替换memcache所做的的兼容
     * @return bool 成功时返回 true， 或者在失败时返回 false。
     */
    public function set($key, $val, $expire = 0, $flag = 0)
    {
        $result = $this->red_obj->set(md5($key), json_encode($val));
        if ($expire) {
            $this->red_obj->expireat(md5($key), $expire);
        }
        return $result;
    }

    /**
     * 从服务端检回一个元素
     *
     * 你可以给get()方法传递一个数组（多个key）来获取一个数组的元素值，返回的数组仅仅包含从 服务端查找到的key-value对。
     *
     * @param string|array $key
     *        要获取值的key或key数组。
     * @param int|array $flags
     *        无意义，为了更好的替换memcache所做的的兼容
     * @return string|array 返回key对应的存储元素的字符串值或者在失败或key未找到的时候返回false。
     */
    public function get($key, $flags = 0)
    {
        if (is_array($key)) {
            $res = array();
            foreach ($key as $val) {
                $res[$val] = json_decode($this->red_obj->get(md5($val)));
            }
            return $res;
        }
        return json_decode($this->red_obj->get(md5($key)));
    }

    /**
     * 替换已经存在的元素的值
     *
     * @param string $key
     *        期望替换值的元素的key。
     * @param mixed $val
     *        将要存储的新的值，字符串和数值直接存储，其他类型序列化后存储。
     * @param int $expire
     *        当前写入缓存的数据的失效时间。如果此值设置为0表明此数据永不过期。你可以设置一个UNIX时间戳或 以秒为单位的整数（从当前算起的时间差）来说明此数据的过期时间，但是在后一种设置方式中，不能超过 2592000秒（30天）。
     * @param int $flag
     *        无意义，为了更好的替换memcache所做的的兼容
     * @return bool 成功时返回 true， 或者在失败时返回 false。
     */
    public function replace($key, $val, $expire = 0, $flag = 0)
    {
        if (!$this->red_obj->exists(md5($key))) {
            return false;
        }
        $result = $this->red_obj->set(md5($key), json_encode($val));
        if ($expire) {
            $this->red_obj->expireat(md5($key), $expire);
        }
        return $result;
    }

    /**
     * 从服务端删除一个元素
     *
     * @param string $key
     *        要删除的元素的key。
     * @return bool 成功时返回 true， 或者在失败时返回 false。
     */
    public function delete($key)
    {
        return $this->red_obj->delete(md5($key));
    }

    /**
     * 增加一个元素的值
     *
     * 不要在经过压缩存储的元素上使用increment()，因为这样作会导致后续对get()的调用失败。
     *
     * @param string $key
     *        将要增加值的元素的key。
     * @param int $val
     *        参数val表明要将指定元素值增加多少。
     * @return int|false 成功时返回新的元素值 或者在失败时返回 false。
     */
    public function increment($key, $val = 1)
    {
        return $this->red_obj->incrby(md5($key), $val);
    }

    /**
     * 减小元素的值
     *
     * 新的元素的值不会小于0。<br/>
     * 不要将decrement()方法用于压缩存储的元素，那样作会导致 get()方法获取值会失败。
     *
     * @param string $key
     *        要减小值的元素的key。
     * @param int $val
     *        val参数指要将指定元素的值减小多少。
     * @return int|false 成功的时候返回元素的新值 或者在失败时返回 false。
     */
    public function decrement($key, $val = 1)
    {
        return $this->red_obj->decrby(md5($key), $val);
    }

    /**
     * 增加一个条目到缓存服务器
     *
     * @param string $key
     *        将要分配给变量的key。
     * @param mixed $val
     *        将要被存储的变量。字符串和整型被以原文存储，其他类型序列化后存储。
     * @param int $flag
     *        无意义，为了更好的替换memcache所做的的兼容
     * @param int $expire
     *        当前写入缓存的数据的失效时间。如果此值设置为0表明此数据永不过期。你可以设置一个UNIX时间戳或 以秒为单位的整数（从当前算起的时间差）来说明此数据的过期时间，但是在后一种设置方式中，不能超过 2592000秒（30天）。
     * @return bool 成功时返回 true， 或者在失败时返回 false. 如果这个key已经存在返回false。
     */
    public function add($key, $val, $flag = 0, $expire = 0)
    {
        if ($this->red_obj->exists(md5($key))) {
            return false;
        }
        $result = $this->red_obj->set(md5($key), json_encode($val));
        if ($expire) {
            $this->red_obj->expireat(md5($key), $expire);
        }
        return $result;
    }

    /**
     * 清洗（删除）已经存储的所有的元素
     *
     * flush()立即使所有已经存在的元素失效。方法flush() 并不会真正的释放任何资源，而是仅仅标记所有元素都失效了，因此已经被使用的内存会被新的元素复写。
     *
     * @return bool 成功时返回 true， 或者在失败时返回 false。
     */
    public function flush()
    {
        return $this->red_obj->flushall();
    }
}