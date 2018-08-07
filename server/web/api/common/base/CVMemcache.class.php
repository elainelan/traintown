<?php
/**
 * 封装memcache操作的文件
 *
 * memcache的连接初始化、设置、删除等操作
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * memcache操作类
 *
 * @author dragonets
 * @package common
 * @subpackage base
 */
class CVMemcache
{

    /**
     * 存储多个memcache连接实例的数组
     *
     * @var array
     */
    private static $s_instance = array();

    /**
     * 单个memcache连接实例
     *
     * @var object Memcache
     */
    private $mem_obj;

    /**
     * memcache连接实例索引
     *
     * @var string
     */
    private $m_cluster_id;

    /**
     * 打开一个memcached服务端连接
     *
     * @param string $cluster_id
     *        实例索引。
     * @param string $host
     *        memcached服务端监听主机地址。这个参数也可以指定为其他传输方式比如unix:///path/to/memcached.sock 来使用Unix域socket，在这种方式下，port参数必须设置为0。
     * @param int $port
     *        memcached服务端监听端口。当使用Unix域socket的时候要设置此参数为0。
     * @param int $threshold
     *        开启大值自动压缩功能。控制多大值进行自动压缩的阈值, 2048 = 2k。
     * @param float $min_saving
     *        指定经过压缩实际存储的值的压缩率，支持的值必须在0和1之间。默认值是0.2表示20%压缩率。
     * @param int $timeout
     *        连接memcache服务端超时时间，默认2s
     * @return void
     */
    public function __construct($cluster_id, $host = MEMCACHE_IP, $port = MEMCACHE_PORT, $threshold = 0, $min_saving = 0.2, $timeout)
    {
        $this->m_cluster_id = $cluster_id;
        $this->mem_obj = new Memcache();
        $res = @$this->mem_obj->connect($host, $port, $timeout);
        if (!$res) {
            throw new CVMemcacheException('Connect memcached failed!', CVMemcacheException::CONNECTION_TIMEOUT);
        }
        if ($threshold && $min_saving) {
            $this->mem_obj->setCompressThreshold($threshold, $min_saving);
        }
    }

    /**
     * 获取一个memcached服务端连接
     *
     * @param string $cluster_id
     *        实例索引。
     * @param string $host
     *        memcached服务端监听主机地址。这个参数也可以指定为其他传输方式比如unix:///path/to/memcached.sock 来使用Unix域socket，在这种方式下，port参数必须设置为0。
     * @param int $port
     *        memcached服务端监听端口。当使用Unix域socket的时候要设置此参数为0。
     * @param int $threshold
     *        开启大值自动压缩功能。控制多大值进行自动压缩的阈值, 2048 = 2k。
     * @param float $min_saving
     *        指定经过压缩实际存储的值的压缩率，支持的值必须在0和1之间。默认值是0.2表示20%压缩率。
     * @param int $timeout
     *        连接memcache服务端超时时间，默认2s
     * @return object Memcache memcached服务端连接实例。
     */
    public static function getInstance($cluster_id, $host = MEMCACHE_IP, $port = MEMCACHE_PORT, $threshold = 10240, $min_saving = 0.2, $timeout = 2)
    {
        if (empty(self::$s_instance[$host . $port . $cluster_id])) {
            self::$s_instance[$host . $port . $cluster_id] = new CVMemcache($cluster_id, $host, $port, $threshold, $min_saving, $timeout);
        }
        return self::$s_instance[$host . $port . $cluster_id];
    }

    /**
     * 返回缓存key
     *
     * @param string $key
     *        缓存原始key
     * @return string
     */
    private function _getKey($key)
    {
        return md5("{$this->m_cluster_id}_{$key}");
    }

    /**
     * 在服务器上存储值
     *
     * @param string $key
     *        要设置值的key。
     * @param mixed $val
     *        要存储的值，字符串和数值直接存储，其他类型序列化后存储。
     * @param int $expires
     *        当前写入缓存的数据的失效时间。如果此值设置为0表明此数据永不过期。你可以设置一个UNIX时间戳或 以秒为单位的整数（从当前算起的时间差）来说明此数据的过期时间，但是在后一种设置方式中，不能超过 2592000秒（30天）。
     * @param int $flag
     *        使用MEMCACHE_COMPRESSED对值进行压缩(使用zlib)。
     * @return bool 成功时返回 true， 或者在失败时返回 false。
     */
    public function set($key, $val, $expires = 0, $flag = 0)
    {
        return $this->mem_obj->set(self::_getKey($key), $val, $flag, $expires);
    }

    /**
     * 从服务端检回一个元素
     *
     * 你可以给get()方法传递一个数组（多个key）来获取一个数组的元素值，返回的数组仅仅包含从 服务端查找到的key-value对。
     *
     * @param string|array $key
     *        要获取值的key或key数组。
     * @param int|array $flags
     *        如果给定这个参数（以引用方式传递），该参数会被写入一些key对应的信息。这些标记和Memcache::set()方法中的同名参数 意义相同。用int值的低位保留了pecl/memcache的内部用法（比如：用来说明压缩和序列化状态）。（译注：最后一位表明是否序列化，倒数第二位表明是否经过压缩， 比如：如果此值为1表示经过序列化，但未经过压缩，2表明压缩而未序列化，3表明压缩并且序列化，0表明未经过压缩和序列化，具体算法可查找linux文件权限算法相关资料）。
     * @return string|array 返回key对应的存储元素的字符串值或者在失败或key未找到的时候返回false。
     */
    public function get($key, $flags = 0)
    {
        if (is_array($key)) {
            $send_key = array();
            foreach ($key as $val) {
                $send_key[] = self::_getKey($val);
            }
            $rs = $this->mem_obj->get($send_key, $flags);
            $res = array();
            $i = 0;
            foreach ($rs as $k => $val) {
                $res[$key[$i]] = $val;
                $i++;
            }
            unset($send_key);
            unset($rs);
            return $res;
        }
        return $this->mem_obj->get(self::_getKey($key), $flags);
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
     *        使用MEMCACHE_COMPRESSED指定对值进行压缩(使用zlib)。
     * @return bool 成功时返回 true， 或者在失败时返回 false。
     */
    public function replace($key, $val, $expire = 0, $flag = 0)
    {
        return $this->mem_obj->replace(self::_getKey($key), $val, $flag, $expire);
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
        if (!$this->mem_obj->delete(self::_getKey($key))) {
            return $this->mem_obj->delete(self::_getKey($key));
        }
        return $this->mem_obj->delete(self::_getKey($key));
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
        return $this->mem_obj->increment(self::_getKey($key), $val);
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
        return $this->mem_obj->decrement(self::_getKey($key), $val);
    }

    /**
     * 增加一个条目到缓存服务器
     *
     * @param string $key
     *        将要分配给变量的key。
     * @param mixed $val
     *        将要被存储的变量。字符串和整型被以原文存储，其他类型序列化后存储。
     * @param int $flag
     *        使用MEMCACHE_COMPRESSED标记对数据进行压缩(使用zlib)。
     * @param int $expire
     *        当前写入缓存的数据的失效时间。如果此值设置为0表明此数据永不过期。你可以设置一个UNIX时间戳或 以秒为单位的整数（从当前算起的时间差）来说明此数据的过期时间，但是在后一种设置方式中，不能超过 2592000秒（30天）。
     * @return bool 成功时返回 true， 或者在失败时返回 false. 如果这个key已经存在返回false。
     */
    public function add($key, $val, $flag = 0, $expire = 0)
    {
        return $this->mem_obj->add(self::_getKey($key), $val, $flag, $expire);
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
        return $this->mem_obj->flush();
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
     * @return boolean 是否获得锁
     */
    public function lock($key, $value = 1, $expire_time = 120, $wait = 60)
    {
        $lock_key = 'lock_queryState_' . $key;
        while (!self::add($lock_key, $value, false, $expire_time) && $wait > 0) {
            sleep(1);
            --$wait;
        }
        if ($wait <= 0) {
            // 未获得锁
            return false;
        }
        // 获得锁
        return true;
    }

    /**
     * 内存解锁
     *
     * @param string $key
     *        之前加锁的key
     * @return boolean
     */
    public function unlock($key)
    {
        $lock_key = 'lock_queryState_' . $key;
        return self::delete($lock_key);
    }
}

/**
 * CVMemcache报错异常类
 *
 * @author dragonets
 */
class CVMemcacheException extends Exception
{

    const CONNECTION_TIMEOUT = 1;
}