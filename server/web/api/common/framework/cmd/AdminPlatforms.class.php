<?php
/**
 * platforms平台处理
 *
 * @author dragonets
 * @package common
 * @subpackage cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 平台处理类
 *
 * 缓存结构：
 * 单个平台信息缓存：cache_key_sub_string : $platid, cache_value: $table_$platid => array(platid=>array(...))
 * 所有平台信息缓存：cache_key_sub_string : null, cache_value: $table_$platid => array(platid=>array(...))
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminPlatforms extends CDbTableBase
{

    /**
     * 所有平台信息
     *
     * @var array
     */
    private static $platforms;

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_platforms';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     * 参考
     *
     * @see CDbTableBase::get()
     * @param int $platid
     *        平台id
     * @param array $where_args
     *        条件数组
     * @param string $idx_key
     *        数据索引key
     */
    function getWithCache($platid = null, $where_args = array(), $idx_key = 'id')
    {
        //$platid = null;
        $where_args = array();
        $idx_key = 'id';
        
        if (!self::$platforms) {
            self::$platforms = parent::getWithCache(null, null, 'id');
        }
        if (strlen($platid) == 0) {
            return self::$platforms;
        }
        else {
            return array($platid => self::$platforms[$platid]);
        }
    }

    function getByCond($where_args = array(), $page = 1, $num = 0)
    {
        // limit处理
        $start = 0;
        $size = 0;
        if (!empty($num) && $num > 0) {
            $start = ($page - 1) * $num;
            $size = $num;
        }
        
        $total = Sql::select('count(1) as sum')->from($this->table)->whereArgs($where_args)->get($this->s_db);
        $data = Sql::select('*')->from($this->table)->whereArgs($where_args)->limit($start, $size)->get($this->s_db, 'id');
        
        return Pagination::formatData($page, ceil($total[0]['sum'] / $size), $size, $total[0]['sum'], $data);
    }

    function getByCondData($where_args = array())
    {
        return Sql::select('*')->from($this->table)->whereArgs($where_args)->get($this->s_db, 'id');
    }

    /**
     * 获取未关服的平台信息
     *
     * @return multitype:array
     */
    function getOpen()
    {
        $data = array();
        $res = $this->getWithCache();
        if ($res) {
            foreach ($res as $k => $v) {
                if ($v['close']) {
                    continue;
                }
                $data[$k] = $v;
            }
        }
        return $data;
    }

    /**
     * 验证平台ID是否正确（从缓存获取）
     *
     * @param int $platid
     *        平台ID
     */
    public function getValidPlatform($platid)
    {
        if (empty($platid)) {
            return false;
        }
        $res = parent::getWithCache($platid, array('id' => $platid), 'id');
        if (!$res || !$res[$platid]) {
            return false;
        }
        return $res[$platid];
    }

    /**
     * 验证平台ID是否可用（从缓存获取）
     *
     * @param int $platid
     *        平台ID
     * @return boolean true: 可用（不存在）；false：不可用（存在）
     */
    public function getAvailablePlatId($platid)
    {
        if (empty($platid)) {
            return false;
        }
        return !parent::validByCondFromDb('id', array('id' => $platid));
    }

    /**
     * 验证平台ID是否可用（从缓存获取）
     *
     * @param string $platname
     *        平台名
     */
    public function getAvailablePlatName($platname)
    {
        if (empty($platname)) {
            return false;
        }
        return !parent::validByCondFromDb('id', array('name' => $platname));
    }

    /**
     * 获取平台通用信息（删除了敏感key的数据）
     *
     * @param int|null $platid        
     * @return array
     */
    public function getCommonInfos($platid)
    {
        $common_infos = array();
        
        $secret_keys = array('game_sig', 'pay_sig');
        $db_res = $this->getWithCache($platid);
        if ($db_res) {
            foreach ($db_res as $k => & $v) {
                $common_infos[$k] = $v;
                foreach ($secret_keys as $secret_key) {
                    unset($common_infos[$k][$secret_key]);
                }
            }
        }
        
        return $common_infos;
    }

    /**
     * 新增平台信息
     *
     * @see CDbTableBase::insert()
     * @param int $platid
     *        平台id
     * @param array $insert_values
     *        插入数据数组
     * @param string $insert_attr
     *        插入方式属性（IGNORE等）
     */
    public function insertWithCacheID($platid, $insert_values, $insert_attr = null)
    {
        return parent::insertWithCacheID($platid, $insert_values, $insert_attr);
    }

    /**
     * 删除平台信息
     *
     * @see CDbTableBase::del()
     * @param int $platid
     *        平台ID
     * @param array $where_args
     *        删除条件数组
     */
    public function delWithCache($platid, $where_args)
    {
        return parent::delWithCache($platid, $where_args);
    }

    /**
     * 修改平台信息
     *
     * @param int $platid
     *        平台ID
     * @param array $update_args
     *        更新数据数组
     * @param array $where_args
     *        更新条件数组
     */
    public function updateWithCache($platid, $update_args, $where_args)
    {
        return parent::updateWithCache($platid, $update_args, $where_args);
    }
}