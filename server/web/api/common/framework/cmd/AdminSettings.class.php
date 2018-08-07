<?php
/**
 * settings处理
 *
 * @author dragonets
 * @package common
 * @subpackage cmd
 */

if (!defined('COM_PATH')) {
    exit('No direct script access allowed');
}

/**
 * 配置处理类
 *
 * 缓存结构：
 * 单个配置信息缓存：cache_key_sub_string : $id, cache_value: $table_$id => array(id=>array(...))
 * 所有配置信息缓存：cache_key_sub_string : null, cache_value: $table_$id => array(id=>array(...))
 *
 * @author dragonets
 * @package common
 * @subpackage framework/cmd
 */
class AdminSettings extends CDbTableBase
{

    /**
     * 所有配置信息
     *
     * @var array
     */
    private static $settings;

    /**
     * 初始化
     */
    function __construct()
    {
        $this->table = 'admin_settings';
        if (isset($this->s_db_default) && $this->s_db_default == 'http') {
            $this->s_db = CDbuser::getInstanceDbHttp($this->table);
        }
        else {
            $this->s_db = CDbuser::getInstanceDbPdo();
        }
    }

    /**
     *
     * 获取配置信息
     *
     * 参考
     *
     * @see CDbTableBase::get()
     * @param int $id
     *        配置id
     * @param array $where_args
     *        条件数组
     * @param string $idx_key
     *        数据索引key
     */
    function getWithCache($id = null, $where_args = array(), $idx_key = 'id')
    {
        //$id = null;
        $where_args = array();
        $idx_key = 'id';
        
        if (!self::$settings) {
            self::$settings = parent::getWithCache(null, null, 'id');
        }
        if (strlen($id) == 0) {
            return self::$settings;
        }
        else {
            return array($id => self::$settings[$id]);
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
     * 新增配置信息
     *
     * @see CDbTableBase::insert()
     * @param int $id
     *        配置id
     * @param array $insert_values
     *        插入数据数组
     * @param string $insert_attr
     *        插入方式属性（IGNORE等）
     */
    public function insertWithCacheID($id, $insert_values, $insert_attr = null)
    {
        return parent::insertWithCacheID($id, $insert_values, $insert_attr);
    }

    /**
     * 删除配置信息
     *
     * @see CDbTableBase::del()
     * @param int $id
     *        配置ID
     * @param array $where_args
     *        删除条件数组
     */
    public function delWithCache($id, $where_args)
    {
        return parent::delWithCache($id, $where_args);
    }

    /**
     * 修改配置信息
     *
     * @param int $id
     *        配置ID
     * @param array $update_args
     *        更新数据数组
     * @param array $where_args
     *        更新条件数组
     */
    public function updateWithCache($id, $update_args, $where_args)
    {
        return parent::updateWithCache($id, $update_args, $where_args);
    }
}